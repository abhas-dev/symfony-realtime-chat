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
        $data = $this->jsonDecode($request->getContent(), true);

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

    function jsonDecode($json, $assoc = false)
    {
        $ret = json_decode($json, $assoc);
        if ($error = json_last_error())
        {
            $errorReference = [
                JSON_ERROR_DEPTH => 'The maximum stack depth has been exceeded.',
                JSON_ERROR_STATE_MISMATCH => 'Invalid or malformed JSON.',
                JSON_ERROR_CTRL_CHAR => 'Control character error, possibly incorrectly encoded.',
                JSON_ERROR_SYNTAX => 'Syntax error.',
                JSON_ERROR_UTF8 => 'Malformed UTF-8 characters, possibly incorrectly encoded.',
                JSON_ERROR_RECURSION => 'One or more recursive references in the value to be encoded.',
                JSON_ERROR_INF_OR_NAN => 'One or more NAN or INF values in the value to be encoded.',
                JSON_ERROR_UNSUPPORTED_TYPE => 'A value of a type that cannot be encoded was given.',
            ];
            $errStr = isset($errorReference[$error]) ? $errorReference[$error] : "Unknown error ($error)";
            throw new \Exception("JSON decode error ($error): $errStr");
        }
        return $ret;
    }
}
