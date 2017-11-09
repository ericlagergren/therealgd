<?php

namespace AppBundle\Controller;

use Doctrine\ORM\EntityManager;
use AppBundle\Entity\User;
use AppBundle\Form\Model\UserData;
use AppBundle\Form\RequestPasswordResetType;
use AppBundle\Form\UserType;
use AppBundle\Mailer\ResetPasswordMailer;
use AppBundle\Repository\UserRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class ResetPasswordController extends AbstractController {
    /**
     * @param Request             $request
     * @param UserRepository      $ur
     * @param ResetPasswordMailer $mailer
     *
     * @return Response
     */
    public function requestReset(Request $request, UserRepository $ur, ResetPasswordMailer $mailer) {
        if (!$mailer->canMail()) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(RequestPasswordResetType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $email = $form->getData()->getEmail();

            // TODO - this is susceptible to timing attacks.
            // TODO - send only one email with all the links.
            foreach ($ur->lookUpByEmail($email) as $user) {
                $mailer->mail($user, $request);
            }

            $this->addFlash('success', 'flash.reset_password_email_sent');

            return $this->redirectToRoute('front');
        }

        return $this->render('reset_password/request.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @ParamConverter("expires", options={"format": "U"}, converter="datetime")
     *
     * @param Request             $request
     * @param EntityManager       $em
     * @param User                $user
     * @param ResetPasswordMailer $mailer
     * @param \DateTime           $expires
     * @param string              $checksum
     *
     * @return Response
     */
    public function reset(
        Request $request, EntityManager $em, User $user,
        ResetPasswordMailer $mailer, \DateTime $expires, string $checksum
    ) {
        if (!$mailer->validateChecksum($checksum, $user, $expires)) {
            throw $this->createNotFoundException('Invalid checksum');
        }

        if (new \DateTime('@'.time()) > $expires) {
            throw $this->createNotFoundException('The link has expired');
        }

        $data = UserData::fromUser($user);

        $form = $this->createForm(UserType::class, $data);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data->updateUser($user);

            $em->flush();

            $this->addFlash('success', 'flash.user_password_updated');

            return $this->redirectToRoute('front');
        }

        return $this->render('reset_password/reset.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
