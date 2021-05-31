<?php
namespace Cocorico\CoreBundle\Controller\Frontend;

use Cocorico\UserBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Cocorico\CoreBundle\Utils\Tracker;

/**
 * Pages Itou controller.
 *
 * @Route("/itou")
 */
class ItouController extends Controller
{

    private $tracker;

    /**
     * Route c'est quoi l'inclusion
     *
     * @Route("/inclusion", name="cocorico_page_inclusion")
     *
     * @Method("GET")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function InclusionPage( Request $request) {
        return $this->render(
            'CocoricoCoreBundle:Frontend/Itou:inclusion.html.twig',
            array(
            )
        );
    
    }

}
