<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\MessageBundle\Model;

use Cocorico\CoreBundle\Entity\Booking;
use Cocorico\MessageBundle\Entity\Thread;
use Cocorico\MessageBundle\MessageBuilder\NewThreadMessageBuilder;
use Cocorico\MessageBundle\MessageBuilder\ReplyMessageBuilder;
use Cocorico\UserBundle\Mailer\TwigSwiftMailer;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use FOS\MessageBundle\EntityManager\MessageManager as FOSMessageManager;
use FOS\MessageBundle\EntityManager\ThreadManager as FOSThreadManager;
use FOS\MessageBundle\Model\MessageInterface;
use FOS\MessageBundle\Model\ParticipantInterface;
use FOS\MessageBundle\Model\ThreadInterface;

/**
 * Default ORM ThreadManager.
 *
 *
 */
class ThreadManager
{
    protected $fosThreadManager;
    protected $fosMessageManager;
    protected $mailer;
    public $maxPerPage;

    /**
     * Constructor.
     *
     * @param FOSThreadManager  $fosThreadManager
     * @param FOSMessageManager $fosMessageManager
     * @param TwigSwiftMailer   $mailer
     * @param integer           $maxPerPage
     */
    public function __construct(
        FOSThreadManager $fosThreadManager,
        FOSMessageManager $fosMessageManager,
        TwigSwiftMailer $mailer,
        $maxPerPage
    ) {
        $this->fosThreadManager = $fosThreadManager;
        $this->fosMessageManager = $fosMessageManager;
        $this->mailer = $mailer;
        $this->maxPerPage = $maxPerPage;
    }

    /**
     * Finds not deleted threads for a participant,
     * containing at least one message not written by this participant,
     * ordered by last message not written by this participant in reverse order.
     * also checks if the threads are not connected to any of the bookings
     * In one word: an inbox.
     *
     * Doctrine bugs:
     *
     * @link https://github.com/doctrine/doctrine2/pull/1220
     * @link http://www.doctrine-project.org/jira/browse/DDC-2890
     * @link https://github.com/doctrine/doctrine2/pull/1151
     *
     *
     * @param ParticipantInterface $participant
     * @param  String              $userType
     * @param  integer             $page
     * @return Paginator object
     */
    public function getListingInboxThreads(ParticipantInterface $participant, $userType = 'asker', $page = 1)
    {
        // get the query builder form the FOSThreadManager
        /** @var QueryBuilder $queryBuilder */
        if ($userType == 'asker') {
            $queryBuilder = $this->fosThreadManager
                ->getParticipantSentThreadsQueryBuilder($participant);
        } else {
            $queryBuilder = $this->fosThreadManager
                ->getParticipantInboxThreadsQueryBuilder($participant);
        }

        //Pagination
        $queryBuilder
            //Requests optimisations? The results are not the same
            //todo: optimize this request
//            ->select('t, tm, p, m, mm')
//            ->innerJoin('t.messages', 'm')
//            ->innerJoin('m.metadata', 'mm')
            ->setFirstResult(($page - 1) * $this->maxPerPage)
            ->setMaxResults($this->maxPerPage);

        //Query
        $query = $queryBuilder->getQuery();

        //Arg fetchJoinCollection setted to false due to Doctrine bug
        //todo: resolve otherwise this problem
        $paginator = new Paginator($query, false);

//        $paginator->setUseOutputWalkers(true);
        return $paginator;
    }

    /**
     * Creates a new listing thread for the booking request by an asker
     * In one word: new listing request.
     *
     * @param ParticipantInterface $participant
     * @param Booking              $booking
     */
    public function createNewListingThread(ParticipantInterface $participant, Booking $booking)
    {
        /** @var  ThreadInterface $thread */
        $thread = $this->fosThreadManager->createThread();
        /** @var MessageInterface $message */
        $message = $this->fosMessageManager->createMessage();

        $threadBuilder = new NewThreadMessageBuilder($message, $thread);
        $listing = $booking->getListing();
        $threadBuilder
            ->addRecipient($listing->getUser())
            ->setSender($participant)
            ->setBooking($booking)
            ->setListing($listing)
            ->setSubject($listing->getTitle())
            ->setBody($booking->getMessage());

        // send the message
        $threadMessage = $threadBuilder->getMessage();
        $this->fosThreadManager->saveThread($threadMessage->getThread(), false);
        $this->fosMessageManager->saveMessage($threadMessage, false);

        $threadMessage->getThread()->setIsDeleted(false);
        $this->fosMessageManager->saveMessage($threadMessage);
    }

    /**
     * replies to the existing booking request with refused or accepted status
     * In one word: booking response.
     *
     * @param Booking              $booking
     * @param string               $messageTxt
     * @param ParticipantInterface $sender
     */
    public function addReplyThread(Booking $booking, $messageTxt, ParticipantInterface $sender)
    {
        /** @var MessageInterface $message */
        $message = $this->fosMessageManager->createMessage();
        $thread = $booking->getThread();
        $replyBuilder = new ReplyMessageBuilder($message, $thread);
        $replyBuilder
            ->setSender($sender)
            ->setBody($messageTxt);
        // send the message
        $threadMessage = $replyBuilder->getMessage();
        $this->fosMessageManager->saveMessage($threadMessage, false);
    }

    /**
     * Get user reply rate and average delay in seconds
     *
     * @param ParticipantInterface $user
     * @return array 'reply_rate' => rate, 'reply_delay' => duration in seconds
     */
    public function getReplyRateAndDelay(ParticipantInterface $user)
    {
        $replyRate = $replyDelay = 0;
        /** @var Thread[] $inboxThreads */
        $inboxThreads = $this->fosThreadManager->findParticipantInboxThreads($user);
        $nbInBox = count($inboxThreads);

        if ($nbInBox) {
            /** @var Thread[] $sendboxThreads */
            $sendboxThreads = $this->fosThreadManager->findParticipantSentThreads($user);
            $nbSendBox = count($sendboxThreads);
            $replyRate = $nbSendBox / $nbInBox;

            foreach ($sendboxThreads as $cpt => $sendboxThread) {
                $threadMetaData = $sendboxThread->getMetadataForParticipant($user);
                /** @var \DateTime $lastReplyDate */
                $lastReplyDate = $threadMetaData->getLastParticipantMessageDate();
                /** @var  \DateTime $lastMsgDate */
                $lastMsgDate = $threadMetaData->getLastMessageDate();
                if ($lastReplyDate && $lastMsgDate) {
                    //todo: check when $lastMsgDate > $lastReplyDate
                    $replyDelay += max($lastReplyDate->getTimestamp() - $lastMsgDate->getTimestamp(), 0);
                }
            }

            if ($nbSendBox) {
                $replyDelay = $replyDelay / $nbSendBox;
            }

        }

        return array(
            "reply_rate" => $replyRate,
            "reply_delay" => round($replyDelay),
        );
    }


}
