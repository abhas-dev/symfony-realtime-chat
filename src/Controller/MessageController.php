<?php

namespace App\Controller;

use App\Entity\Message;
use App\Repository\ChannelRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route(name: 'message_')]
class MessageController extends AbstractController
{
    public function __construct(
        private readonly ChannelRepository $channelRepository,
        private readonly SerializerInterface $serializer,
        private readonly EntityManagerInterface $manager
    )
    {
    }

    #[Route('/message', name: 'send', methods: ['POST'])]
    public function send(Request $request): JsonResponse
    {
        // On récupère les data postées et on les déserialize
        $data = \json_decode($request->getContent(), true);
        if (empty($content = $data['content'])) {
            throw new AccessDeniedHttpException('Aucune donnée reçue');
        }

        // On cherche à savoir de quel channel provient le message
        $channel = $this->channelRepository->findOneBy([
            'id' => $data['channel']
        ]);
        if (!$channel) {
            throw new AccessDeniedHttpException('Un message doit etre envoyé sur un canal précis');
        }

        $message = new Message();
        $message->setContent($content);
        $message->setChannel($channel);
        $message->setAuthor($this->getUser());

        $this->manager->persist($message);
        $this->manager->flush();

        // On serialize la réponse avant de la renvoyer
        $jsonMessage = $this->serializer->serialize($message, 'json', [
            'groups' => ['message']
        ]);

        return new JsonResponse(
            $jsonMessage,
            Response::HTTP_OK,
            [],
            true
        );
    }
}
