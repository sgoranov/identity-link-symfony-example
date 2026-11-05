<?php

namespace App\Controller;

use Drenso\OidcBundle\Exception\OidcException;
use Drenso\OidcBundle\OidcClientInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

final class SecurityController extends AbstractController
{
    #[Route('/connect/identity-link', name: 'connect_identity_link_start')]
    public function connect(OidcClientInterface $oidcClient, LoggerInterface $logger): RedirectResponse
    {
        try {
            return $oidcClient->generateAuthorizationRedirect(scopes: ['openid', 'profile', 'email']);
        } catch (OidcException|AuthenticationException $exception) {
            // The bundle could not load Identity Link's discovery document, e.g. because Identity Link is down. It
            // reports a connection error as an AuthenticationException, which would restart the sign-in in a loop
            // if it reached the firewall.
            $logger->error('Cannot start signing in with Identity Link.', ['exception' => $exception]);

            return $this->redirectToRoute('connect_identity_link_failed');
        }
    }

    #[Route('/connect/identity-link/check', name: 'connect_identity_link_check')]
    public function callback(Request $request): RedirectResponse
    {
        // The bundle's authenticator handles a callback with a code and state before this runs. Only other requests
        // get here, such as Identity Link's redirect with an error instead of a code.
        $parameters = $request->query->get('error') === 'access_denied' ? ['reason' => 'cancelled'] : [];

        return $this->redirectToRoute('connect_identity_link_failed', $parameters);
    }

    #[Route('/connect/identity-link/failed', name: 'connect_identity_link_failed')]
    public function failed(Request $request): Response
    {
        return $this->render('security/sign_in_failed.html.twig', [
            'cancelled' => $request->query->get('reason') === 'cancelled',
        ]);
    }

    #[Route('/signed-out', name: 'app_signed_out')]
    public function signedOut(): Response
    {
        return $this->render('security/signed_out.html.twig');
    }
}
