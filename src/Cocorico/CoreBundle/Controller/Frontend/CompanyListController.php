<?php
namespace Cocorico\CoreBundle\Controller\Frontend;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CompanyListController extends Controler
{
    /**
     * List companies in Database
     *
     * @Route("/list/siae", name="cocorico_itou_siae_list")
     * @Method("GET")
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction(Request $request)
    {
        return $this->render(
            'CocoricoCoreBundle:Frontend\Directory:dir_siae.html.twig'
        );
    
    }

}
?>
