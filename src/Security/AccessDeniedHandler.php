<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Authorization\AccessDeniedHandlerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AccessDeniedHandler extends AbstractController implements AccessDeniedHandlerInterface
{
    public function handle(Request $request, \Symfony\Component\Security\Core\Exception\AccessDeniedException $accessDeniedException): Response
    {

        if ($accessDeniedException instanceof NotFoundHttpException) {
            return $this->render('/common/404.html.twig');
        }

        return $this->render('/common/unauthorized.html.twig');
    }
}
