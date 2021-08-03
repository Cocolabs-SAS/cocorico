<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\CoreBundle\Controller\Frontend;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Cocorico\CoreBundle\Model\DirectorySearchRequest;
use Cocorico\CoreBundle\Form\Type\Frontend\DirectoryQuickFilterType;

/**
 * Class HomeController
 *
 */
class HomeController extends Controller
{
    /**
     * @Route("/", name="cocorico_home")
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request)
    {
        $listings = $this->get("cocorico.listing_search.manager")->getHighestRanked(
            $this->get('cocorico.listing_search_request'),
            42,
            $request->getLocale(),
            $this->getParameter('cocorico.listing_highestrank_cache_age')
        );

        $structures = $this->get("cocorico.directory.manager")->getSomeRandom(4);
        dump($structures);

        

        $directorySearchRequest = $this->get('cocorico.directory_search_request');

        $form = $this->sortCompaniesForm($directorySearchRequest);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            return new RedirectResponse(
                $this->generateUrl(
                    'cocorico_itou_siae_directory',
                    $form->getData()
                )
            );
        }

        return $this->render(
            'CocoricoCoreBundle:Frontend\Home:index_itou.html.twig', [
            'form' => $form->createView(),
            'structures' => $structures,
            ]
        );
    }

    private function sortCompaniesForm(DirectorySearchRequest $directorySearchRequest)
    {
        $form = $this->get('form.factory')->createNamed(
            '',
            DirectoryQuickFilterType::class,
            $directorySearchRequest,
            array(
                'action' => $this->generateUrl(
                    'cocorico_itou_siae_directory',
                    array('page' => 1)
                ),
                'method' => 'GET',
            )
        );
        return $form;
    }

    /**
     * @Route("/home_itou", name="cocorico_home_itou")
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexItouAction(Request $request)
    {
        $listings = $this->get("cocorico.listing_search.manager")->getHighestRanked(
            $this->get('cocorico.listing_search_request'),
            42,
            $request->getLocale(),
            $this->getParameter('cocorico.listing_highestrank_cache_age')
        );

        return $this->render(
            'CocoricoCoreBundle:Frontend\Home:index_itou.html.twig',
            array(
                'listings' => $listings->getIterator(),
            )
        );
    }


    /**
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function rssFeedsAction()
    {
        $feed = $this->getParameter('cocorico.home_rss_feed');
        if (!$feed) {
            return new Response();
        }

        $cacheTime = 3600 * 12;
        $cacheDir = $this->getParameter('kernel.cache_dir');
        $cacheFile = $cacheDir . '/rss-home-feed.json';
        $timeDif = @(time() - filemtime($cacheFile));
        $renderFeeds = array();

        if (file_exists($cacheFile) && $timeDif < $cacheTime) {
            $renderFeeds = json_decode(@file_get_contents($cacheFile), true);
        } else {
            $options = array(
                'http' => array(
                    'user_agent' => 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:40.0) Gecko/20100101 Firefox/40.1',
                    'timeout' => 5,
                ),
            );

            $content = @file_get_contents($feed, false, stream_context_create($options));

            $feeds = array();
            if ($content) {
                try {
                    $feeds = new \SimpleXMLElement($content);
                    $feeds = $feeds->channel->xpath('//item');
                } catch (\Exception $e) {
                    // silently fail error
                }
            }

            /**
             * @var                    $key
             * @var  \SimpleXMLElement $feed
             */
            foreach ($feeds as $key => $feed) {
                $renderFeeds[$key]['title'] = (string)$feed->children()->title;
                $renderFeeds[$key]['pubDate'] = (string)$feed->children()->pubDate;
                $renderFeeds[$key]['link'] = (string)$feed->children()->link;
                $description = $feed->children()->description;
                $matches = [];
                preg_match('/src="([^"]+)"/', $description, $matches);
                if (count($matches)) {
                    $renderFeeds[$key]['image'] = str_replace('http:', '', $matches[1]);
                }
            }

            @file_put_contents($cacheFile, json_encode($renderFeeds));
        }


        return $this->render(
            'CocoricoCoreBundle:Frontend/Home:rss_feed.html.twig',
            array(
                'feeds' => $renderFeeds,
            )
        );
    }

}
