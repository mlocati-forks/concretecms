<?php

declare(strict_types=1);

namespace Concrete\Block\CoreConversation;

use Concrete\Core\Api\Block\DefaultBlockApiHandler;
use Concrete\Core\Block\Block;
use Concrete\Core\Conversation\Conversation;
use Concrete\Core\File\Service\Application as FileService;
use Concrete\Core\User\UserInfoRepository;

defined('C5_EXECUTE') or die('Access Denied.');

class Api extends DefaultBlockApiHandler
{
    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Api\Block\DefaultBlockApiHandler::getApiValueSchema()
     */
    public function getApiValueSchema(): array
    {
        $schema = parent::getApiValueSchema();
        // the conversation belongs to the block: a client can neither choose it nor move the block to another one
        $schema['properties']['cnvID']['readOnly'] = true;
        $schema['properties'] += [
            'attachmentOverridesEnabled' => [
                'type' => 'boolean',
                'description' => 'true: the messages accept the attachments described by the other attachment settings; false: the ones the site allows.',
            ],
            'attachmentsEnabled' => [
                'type' => 'boolean',
                'description' => 'true: a message can carry attachments; false: it can\'t (used if attachmentOverridesEnabled is true).',
            ],
            'fileExtensions' => [
                'type' => 'array',
                'description' => 'The extensions of the files that can be attached to a message, without the leading period (used if attachmentOverridesEnabled is true).',
                'items' => ['type' => 'string'],
            ],
            'maxFilesGuest' => [
                'type' => 'integer',
                'minimum' => 0,
                'description' => 'How many files a visitor who isn\'t logged in can attach to a message (0: the current setting is kept).',
            ],
            'maxFilesRegistered' => [
                'type' => 'integer',
                'minimum' => 0,
                'description' => 'How many files a registered user can attach to a message (0: the current setting is kept).',
            ],
            'maxFileSizeGuest' => [
                'type' => 'integer',
                'minimum' => 0,
                'description' => 'How big a file attached by a visitor who isn\'t logged in can be, in megabytes (0: the current setting is kept).',
            ],
            'maxFileSizeRegistered' => [
                'type' => 'integer',
                'minimum' => 0,
                'description' => 'How big a file attached by a registered user can be, in megabytes (0: the current setting is kept).',
            ],
            'notificationOverridesEnabled' => [
                'type' => 'boolean',
                'description' => 'true: the users listed in notificationUsers are told about the new messages; false: the site decides who is told.',
            ],
            'notificationUsers' => [
                'type' => 'array',
                'description' => 'The users told about the new messages, named by their ID (used if notificationOverridesEnabled is true).',
                'items' => ['type' => 'integer'],
            ],
            'subscriptionEnabled' => [
                'type' => 'boolean',
                'description' => 'true: a registered user can ask to be told about the new messages; false: they can\'t (used if notificationOverridesEnabled is true).',
            ],
        ];

        return $schema;
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Api\Block\DefaultBlockApiHandler::getApiValue()
     */
    public function getApiValue(Block $block): array
    {
        $value = parent::getApiValue($block);
        $conversation = $this->getConversation($block);
        $notificationUsers = [];
        $fileExtensions = [];
        if ($conversation !== null) {
            foreach ($conversation->getConversationSubscribedUsers() as $userInfo) {
                $notificationUsers[] = (int) $userInfo->getUserID();
            }
            $fileExtensions = app(FileService::class)->unSerializeUploadFileExtensions($conversation->getConversationFileExtensions());
        }

        return $value + [
            'attachmentOverridesEnabled' => $conversation !== null && (bool) $conversation->getConversationAttachmentOverridesEnabled(),
            'attachmentsEnabled' => $conversation !== null && (bool) $conversation->getConversationAttachmentsEnabled(),
            'fileExtensions' => array_values(array_map('strval', $fileExtensions)),
            'maxFilesGuest' => $conversation === null ? 0 : (int) $conversation->getConversationMaxFilesGuest(),
            'maxFilesRegistered' => $conversation === null ? 0 : (int) $conversation->getConversationMaxFilesRegistered(),
            'maxFileSizeGuest' => $conversation === null ? 0 : (int) $conversation->getConversationMaxFileSizeGuest(),
            'maxFileSizeRegistered' => $conversation === null ? 0 : (int) $conversation->getConversationMaxFileSizeRegistered(),
            'notificationOverridesEnabled' => $conversation !== null && (bool) $conversation->getConversationNotificationOverridesEnabled(),
            'notificationUsers' => $notificationUsers,
            'subscriptionEnabled' => $conversation !== null && (bool) $conversation->getConversationSubscriptionEnabled(),
        ];
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Api\Block\DefaultBlockApiHandler::getSaveArgumentsFromApiValue()
     */
    public function getSaveArgumentsFromApiValue(array $value, ?Block $block): array
    {
        $value += $block === null ? [] : $this->getApiValue($block);
        $arguments = parent::getSaveArgumentsFromApiValue($value, $block);
        // the save() method looks the conversation of the block up by itself
        unset($arguments['cnvID']);
        $fileExtensions = is_array($value['fileExtensions'] ?? null) ? $value['fileExtensions'] : [];
        $notificationUsers = is_array($value['notificationUsers'] ?? null) ? $value['notificationUsers'] : [];

        return $arguments + [
            'attachmentOverridesEnabled' => empty($value['attachmentOverridesEnabled']) ? 0 : 1,
            'attachmentsEnabled' => empty($value['attachmentsEnabled']) ? 0 : 1,
            'fileExtensions' => implode(',', array_map('strval', $fileExtensions)),
            'maxFilesGuest' => (int) ($value['maxFilesGuest'] ?? 0),
            'maxFilesRegistered' => (int) ($value['maxFilesRegistered'] ?? 0),
            'maxFileSizeGuest' => (int) ($value['maxFileSizeGuest'] ?? 0),
            'maxFileSizeRegistered' => (int) ($value['maxFileSizeRegistered'] ?? 0),
            'notificationOverridesEnabled' => empty($value['notificationOverridesEnabled']) ? 0 : 1,
            'notificationUsers' => $this->getNotificationUserIDs($notificationUsers),
            'subscriptionEnabled' => empty($value['subscriptionEnabled']) ? 0 : 1,
        ];
    }

    private function getConversation(Block $block): ?Conversation
    {
        $row = $this->getMainTableRow($block);
        $conversation = empty($row['cnvID']) ? null : Conversation::getByID((int) $row['cnvID']);

        return $conversation instanceof Conversation ? $conversation : null;
    }

    /**
     * @param array<int|string> $userIDs
     * @return int[]
     */
    private function getNotificationUserIDs(array $userIDs): array
    {
        $userInfoRepository = app(UserInfoRepository::class);
        $result = [];
        foreach ($userIDs as $userID) {
            $userInfo = $userInfoRepository->getByID((int) $userID);
            if ($userInfo !== null) {
                $result[] = (int) $userInfo->getUserID();
            }
        }

        return $result;
    }
}
