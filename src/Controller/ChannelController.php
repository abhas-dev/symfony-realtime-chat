<?php

namespace App\Controller;

use App\Entity\Channel;
use App\Repository\ChannelRepository;
use App\Repository\MessageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(name: 'channel_')]
class ChannelController extends AbstractController
{
    public function __construct(
        private readonly ChannelRepository $channelRepository,
        private readonly MessageRepository $messageRepository
    )
    {
    }

    #[Route('/', name: 'home')]
    public function getChannels(): Response
    {
        $channels = $this->channelRepository->findAll();

        return $this->render('channel/index.html.twig', ['channels' => $channels ?? []]);
    }

    #[Route('/chat/{id}', name: 'chat')]
    public function chat(Channel $channel): Response
    {
        $messages = $this->messageRepository->findBy([
            'channel' => $channel
        ], ['createdAt' => 'ASC']);
        return $this->render('channel/chat.html.twig', compact('channel', 'messages'));
    }
}
