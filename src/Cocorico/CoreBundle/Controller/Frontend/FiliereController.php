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
 * Filiere controller.
 *
 * @Route("/filiere")
 */
class FiliereController extends Controller
{

    private $tracker;

    /**
     * Route filière recyclage
     *
     * @Route("/recyclage", name="cocorico_filiere_recyclage")
     *
     * @Method("GET")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function RecyclagePage( Request $request) {
        return $this->render(
            'CocoricoCoreBundle:Frontend/Filiere:recyclage.html.twig',
            array(
            )
        );
    
    }

    /*
     * @Route("/", name="cocorico_filiere_home")
     *
     * @Method({"GET"})
     *
     * @param Request   $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function HomePage( Request $request) {
        return '';
    }

}
