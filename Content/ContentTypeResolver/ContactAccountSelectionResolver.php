<?php

declare(strict_types=1);

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\HeadlessBundle\Content\ContentTypeResolver;

use JMS\Serializer\SerializationContext;
use Sulu\Bundle\ContactBundle\Contact\AccountManager;
use Sulu\Bundle\ContactBundle\Contact\ContactManager;
use Sulu\Bundle\HeadlessBundle\Content\ContentView;
use Sulu\Bundle\HeadlessBundle\Content\Serializer\AccountSerializer;
use Sulu\Bundle\HeadlessBundle\Content\Serializer\ContactSerializer;
use Sulu\Component\Content\Compat\PropertyInterface;

class ContactAccountSelectionResolver implements ContentTypeResolverInterface
{
    public static function getContentType(): string
    {
        return 'contact_account_selection';
    }

    /**
     * @var ContactManager
     */
    private $contactManager;

    /**
     * @var AccountManager
     */
    private $accountManager;

    /**
     * @var ContactSerializer
     */
    private $contactSerializer;

    /**
     * @var AccountSerializer
     */
    private $accountSerializer;

    public function __construct(
        ContactManager $contactManager,
        AccountManager $accountManager,
        ContactSerializer $contactSerializer,
        AccountSerializer $accountSerializer
    ) {
        $this->contactManager = $contactManager;
        $this->accountManager = $accountManager;
        $this->contactSerializer = $contactSerializer;
        $this->accountSerializer = $accountSerializer;
    }

    public function resolve($data, PropertyInterface $property, string $locale, array $attributes = []): ContentView
    {
        if (null === $data) {
            return new ContentView([]);
        }

        $content = [];
        foreach ($data as $entry) {
            $serializationContext = new SerializationContext();
            if (0 === strncmp($entry, 'c', 1)) {
                $contact = $this->contactManager->getById(substr($entry, 1), $locale);
                $serializationContext->setGroups(['partialContact']);

                $content[] = $this->contactSerializer->serialize($contact, $locale, $serializationContext);
            } elseif (0 === strncmp($entry, 'a', 1)) {
                $account = $this->accountManager->getById((int) substr($entry, 1), $locale);
                $serializationContext->setGroups(['partialAccount']);

                $content[] = $this->accountSerializer->serialize($account, $locale, $serializationContext);
            }
        }

        return new ContentView($content, $data ?? []);
    }
}