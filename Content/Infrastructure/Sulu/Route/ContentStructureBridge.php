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

namespace Sulu\Bundle\ContentBundle\Content\Infrastructure\Sulu\Route;

use Sulu\Bundle\ContentBundle\Content\Domain\Model\TemplateInterface;
use Sulu\Component\Content\Compat\Structure\LegacyPropertyFactory;
use Sulu\Component\Content\Compat\StructureInterface;
use Sulu\Component\Content\Document\Behavior\ShadowLocaleBehavior;
use Sulu\Component\Content\Document\Behavior\StructureBehavior;
use Sulu\Component\Content\Document\Behavior\WorkflowStageBehavior;
use Sulu\Component\Content\Document\RedirectType;
use Sulu\Component\Content\Document\Structure\PropertyValue;
use Sulu\Component\Content\Document\WorkflowStage;
use Sulu\Component\Content\Metadata\StructureMetadata;

class ContentStructureBridge implements StructureInterface
{
    /**
     * @var StructureMetadata
     */
    protected $structure;

    /**
     * @var LegacyPropertyFactory
     */
    private $propertyFactory;

    /**
     * @var TemplateInterface
     */
    protected $content;

    /**
     * @var string
     */
    protected $id;

    /**
     * @var string
     */
    protected $locale;

    /**
     * @var array
     */
    private $loadedProperties = [];

    public function __construct(
        StructureMetadata $structure,
        LegacyPropertyFactory $propertyFactory,
        TemplateInterface $content,
        string $id,
        string $locale
    ) {
        $this->structure = $structure;
        $this->propertyFactory = $propertyFactory;
        $this->content = $content;
        $this->id = $id;
        $this->locale = $locale;
    }

    public function getDocument()
    {
        return new ContentDocument($this->content, $this->locale);
    }

    public function setLanguageCode($locale)
    {
        $this->locale = $locale;
    }

    public function getLanguageCode()
    {
        return $this->locale;
    }

    public function setWebspaceKey($webspace)
    {
        $this->readOnlyException(__METHOD__);
    }

    public function getWebspaceKey()
    {
        return null;
    }

    public function getUuid()
    {
        return $this->id;
    }

    public function setUuid($uuid)
    {
        $this->readOnlyException(__METHOD__);
    }

    public function getView()
    {
        return $this->structure->getView();
    }

    public function getCreator()
    {
        return null;
    }

    public function setCreator($userId)
    {
        $this->readOnlyException(__METHOD__);
    }

    public function getChanger()
    {
        return null;
    }

    public function setChanger($userId)
    {
        $this->readOnlyException(__METHOD__);
    }

    public function getCreated()
    {
        return null;
    }

    public function setCreated(\DateTime $created)
    {
        $this->readOnlyException(__METHOD__);
    }

    public function getChanged()
    {
        return null;
    }

    public function setChanged(\DateTime $changed)
    {
        $this->readOnlyException(__METHOD__);
    }

    public function getKey()
    {
        return $this->structure->getName();
    }

    public function getInternal()
    {
        return $this->structure->isInternal();
    }

    public function getProperty($name)
    {
        if ($this->hasProperty($name)) {
            $property = $this->structure->getProperty($name);
        } else {
            $property = $this->structure->getChild($name);
        }

        return $this->createLegacyPropertyFromItem($property);
    }

    public function hasProperty($name)
    {
        return $this->structure->hasProperty($name);
    }

    public function getProperties($flatten = false)
    {
        if ($flatten) {
            $items = $this->structure->getProperties();
        } else {
            $items = $this->structure->getChildren();
        }

        $propertyBridges = [];
        foreach ($items as $property) {
            $propertyBridges[$property->getName()] = $this->createLegacyPropertyFromItem($property);
        }

        return $propertyBridges;
    }

    public function getExt()
    {
        return new ExtensionContainer($this->getDocument()->getExtensionsData());
    }

    public function setExt($data)
    {
        $this->readOnlyException(__METHOD__);
    }

    public function setHasChildren($hasChildren)
    {
        $this->readOnlyException(__METHOD__);
    }

    public function getHasChildren()
    {
        return false;
    }

    public function setChildren($children)
    {
        $this->readOnlyException(__METHOD__);
    }

    public function getChildren()
    {
        return [];
    }

    public function getParent()
    {
        return null;
    }

    public function getPublishedState()
    {
        return true;
    }

    public function setPublished($published)
    {
        $this->readOnlyException(__METHOD__);
    }

    public function getPublished()
    {
        return null;
    }

    public function getPropertyValue($name)
    {
        return $this->getProperty($name)->getValue();
    }

    public function getPropertyNames()
    {
        return array_keys($this->structure->getChildren());
    }

    public function setType($type)
    {
        $this->readOnlyException(__METHOD__);
    }

    public function getType()
    {
        return null;
    }

    public function getPath()
    {
        return null;
    }

    public function setPath($path)
    {
        $this->readOnlyException(__METHOD__);
    }

    public function setHasTranslation($hasTranslation)
    {
        $this->readOnlyException(__METHOD__);
    }

    public function getHasTranslation()
    {
        return true;
    }

    public function toArray($complete = true)
    {
        return [];
    }

    public function jsonSerialize()
    {
        return $this->toArray(true);
    }

    public function getPropertyByTagName($tagName, $highest = true)
    {
        return $this->createLegacyPropertyFromItem($this->structure->getPropertyByTagName($tagName, $highest));
    }

    public function getPropertiesByTagName($tagName)
    {
        $properties = [];
        foreach ($this->structure->getPropertiesByTagName($tagName) as $structureProperty) {
            $properties[] = $this->createLegacyPropertyFromItem($structureProperty);
        }

        return $properties;
    }

    public function getPropertyValueByTagName($tagName)
    {
        return $this->getPropertyByTagName($tagName)->getValue();
    }

    public function hasTag($tag)
    {
        return $this->structure->hasPropertyWithTagName($tag);
    }

    public function getNodeType()
    {
        return RedirectType::NONE;
    }

    public function getNodeName()
    {
        return null;
    }

    public function getLocalizedTitle($languageCode)
    {
        return null;
    }

    public function getNodeState()
    {
        return WorkflowStage::PUBLISHED;
    }

    public function getTitle()
    {
        return $this->getContent()->getTitle();
    }

    public function getUrl()
    {
        return $this->getContent()->getResourceSegment();
    }

    public function copyFrom(StructureInterface $structure)
    {
        foreach ($this->getProperties(true) as $property) {
            if ($structure->hasProperty($property->getName())) {
                $property->setValue($structure->getPropertyValue($property->getName()));
            }
        }

        $this->content = $structure->getContent();
    }

    public function __get($name)
    {
        return $this->getProperty($name)->getValue();
    }

    public function getShadowLocales()
    {
        return [];
    }

    public function getContentLocales()
    {
        return [];
    }

    public function getIsShadow()
    {
        if (!$this->content) {
            return false;
        }

        $document = $this->getContent();
        if (!$document instanceof ShadowLocaleBehavior) {
            return false;
        }

        return $document->isShadowLocaleEnabled();
    }

    public function getShadowBaseLanguage()
    {
        $document = $this->getContent();
        if (!$document instanceof ShadowLocaleBehavior) {
            return;
        }

        return $document->getShadowLocale();
    }

    public function getResourceLocator()
    {
        $document = $this->getContent();
        if (RedirectType::EXTERNAL === $document->getRedirectType()) {
            return $document->getRedirectExternal();
        }

        if (RedirectType::INTERNAL === $document->getRedirectType()) {
            $target = $document->getRedirectTarget();

            if (!$target) {
                throw new \RuntimeException('Document is an internal redirect, but no redirect target has been set.');
            }

            return $target->getResourceSegment();
        }

        return $document->getResourceSegment();
    }

    /**
     * Returns document.
     *
     * @return object
     */
    public function getContent()
    {
        if (!$this->content) {
            throw new \RuntimeException(
                'Document has not been applied to structure yet, cannot retrieve data from structure.'
            );
        }

        return $this->content;
    }

    /**
     * Returns structure metadata.
     *
     * @return StructureMetadata
     */
    public function getStructure()
    {
        return $this->structure;
    }

    protected function readOnlyException($method)
    {
        throw new \BadMethodCallException(
            sprintf(
                'Compatibility layer StructureBridge instances are readonly. Tried to call "%s"',
                $method
            )
        );
    }

    /**
     * @param StructureBehavior $document The document to convert
     *
     * @return $this
     */
    protected function documentToStructure(StructureBehavior $document)
    {
        return new $this(
            $this->inspector->getStructureMetadata($document),
            $this->inspector,
            $this->propertyFactory,
            $document
        );
    }

    private function getWorkflowDocument($method)
    {
        $document = $this->getContent();
        if (!$document instanceof WorkflowStageBehavior) {
            throw new \BadMethodCallException(
                sprintf(
                    'Cannot call "%s" on Document which does not implement PageInterface. Is "%s"',
                    $method,
                    \get_class($document)
                )
            );
        }

        return $document;
    }

    private function notImplemented($method)
    {
        throw new \InvalidArgumentException(
            sprintf(
                'Method "%s" is not yet implemented',
                $method
            )
        );
    }

    private function normalizeData(array $data = null)
    {
        if (null === $data) {
            return;
        }

        if (false === \is_array($data)) {
            return $this->normalizeValue($data);
        }

        foreach ($data as &$value) {
            if (\is_array($value)) {
                foreach ($value as $childKey => $childValue) {
                    $data[$childKey] = $this->normalizeData($childValue);
                }
            }

            $value = $this->normalizeValue($value);
        }

        return $data;
    }

    private function normalizeValue($value)
    {
        if ($value instanceof StructureBehavior) {
            return $this->documentToStructure($value);
        }

        return $value;
    }

    private function createLegacyPropertyFromItem($item)
    {
        $name = $item->getName();
        if (isset($this->loadedProperties[$name])) {
            return $this->loadedProperties[$name];
        }

        $propertyBridge = $this->propertyFactory->createProperty($item, $this);

        if ($this->content) {
            $property = new PropertyValue($name, $this->content->getTemplateData()[$name] ?? null);
            $propertyBridge->setPropertyValue($property);
        }

        $this->loadedProperties[$name] = $propertyBridge;

        return $propertyBridge;
    }
}
