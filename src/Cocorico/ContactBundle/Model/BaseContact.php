<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\ContactBundle\Model;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * BaseContact
 *
 * @ORM\MappedSuperclass()
 *
 */
abstract class BaseContact
{
    /* Status */
    const STATUS_NEW = 1;
    const STATUS_READ = 2;

    public static $statusValues = array(
        self::STATUS_NEW => 'entity.contact.status.new',
        self::STATUS_READ => 'entity.contact.status.read',
    );

    /**
     * @var string
     *
     * @ORM\Column(name="first_name", type="string", length=255, nullable=false)
     *
     * @Assert\NotBlank(message="cocorico_contact.first_name.blank", groups={"CocoricoContact"})
     *
     * @Assert\Length(
     *     min=3,
     *     max="255",
     *     minMessage="cocorico_contact.first_name.short",
     *     maxMessage="cocorico_contact.first_name.long",
     *     groups={"CocoricoContact"}
     * )
     */
    protected $firstName;

    /**
     * @var string
     *
     * @ORM\Column(name="last_name", type="string", length=255, nullable=false)
     *
     * @Assert\NotBlank(message="cocorico_contact.last_name.blank", groups={"CocoricoContact"})
     *
     * @Assert\Length(
     *     min=3,
     *     max="255",
     *     minMessage="cocorico_contact.last_name.short",
     *     maxMessage="cocorico_contact.last_name.long",
     *     groups={"CocoricoContact"}
     * )
     */
    protected $lastName;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=1024)
     *
     * @Assert\Email(message="cocorico_contact.email.invalid", groups={"CocoricoContact"})
     *
     * @Assert\NotBlank(message="cocorico_contact.email.blank", groups={"CocoricoContact"})
     *
     * @Assert\Length(
     *     min=3,
     *     max="1024",
     *     minMessage="cocorico_contact.email.short",
     *     maxMessage="cocorico_contact.email.long",
     *     groups={"CocoricoContact"}
     * )
     */
    protected $email;

    /**
     * @var string
     *
     * @ORM\Column(name="phone", type="string", length=20, nullable=true)
     *
     * @Assert\Length(
     *     min=3,
     *     max="20",
     *     minMessage="cocorico_contact.phone.short",
     *     maxMessage="cocorico_contact.phone.long",
     *     groups={"CocoricoContact"}
     * )
     */
    protected $phone;

    /**
     * @var string
     *
     * @ORM\Column(name="subject", type="string", length=1024)
     *
     * @Assert\NotBlank(message="cocorico_contact.subject.blank", groups={"CocoricoContact"})
     *
     * @Assert\Length(
     *     min=3,
     *     max="1024",
     *     minMessage="cocorico_contact.subject.short",
     *     maxMessage="cocorico_contact.subject.long",
     *     groups={"CocoricoContact"}
     * )
     */
    protected $subject;

    /**
     * @var string
     *
     * @ORM\Column(name="message", type="text")
     *
     * @Assert\NotBlank(message="cocorico_contact.message.blank", groups={"CocoricoContact"})
     *
     * @Assert\Length(
     *     min=3,
     *     minMessage="cocorico_contact.message.short",
     *     groups={"CocoricoContact"}
     * )
     */
    protected $message;

    /**
     * @ORM\Column(name="status", type="smallint")
     *
     * @var integer
     */
    protected $status = self::STATUS_NEW;


    /**
     * Gets First Name
     *
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * Sets FirstName
     *
     * @param string $firstName the first name
     *
     * @return self
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * Gets Last Name
     *
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * Sets Last Name
     *
     * @param string $lastName the last name
     *
     * @return self
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * Gets Email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Sets Email
     *
     * @param string $email the email
     *
     * @return self
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Gets Phone
     *
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Sets Phone
     *
     * @param string $phone the phone
     *
     * @return self
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * Gets Subject
     *
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * Sets Subject
     *
     * @param string $subject the subject
     *
     * @return self
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * Gets Message
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Sets Subject
     *
     * @param string $message the message
     *
     * @return self
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Gets the value of status.
     *
     * @return integer
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Sets the value of status.
     *
     * @param integer $status the status
     *
     * @return self
     */
    public function setStatus($status)
    {
        if (!in_array($status, array_keys(self::$statusValues))) {
            throw new \InvalidArgumentException(
                sprintf('Invalid value for contact.status : %s.', $status)
            );
        }

        $this->status = $status;

        return $this;
    }

    /**
     * Get Status Text
     *
     * @return string
     */
    public function getStatusText()
    {
        return self::$statusValues[$this->getStatus()];
    }
}
