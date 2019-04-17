<?php

declare(strict_types=1);

namespace Bolt\Widget;

use Bolt\Snippet\Target;
use Bolt\Snippet\Zone;
use Cocur\Slugify\Slugify;
use Symfony\Component\HttpFoundation\Response;

class BaseWidget implements WidgetInterface
{
    protected $name = 'Nameless widget';
    protected $type = 'widget';
    protected $target = Target::NOWHERE;
    protected $zone = Zone::EVERYWHERE;
    protected $priority = 0;
    protected $context = [];

    /** @var string */
    protected $template;

    /** @var Response */
    protected $response;

    /** @var ?string */
    protected $slug;

    public function setName(string $name): WidgetInterface
    {
        $this->name = $name;
        $this->slug = null;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setType(string $type): WidgetInterface
    {
        $this->type = $type;

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setTarget(string $target): WidgetInterface
    {
        $this->target = $target;

        return $this;
    }

    public function getTarget(): string
    {
        return $this->target;
    }

    public function setPriority(int $priority): WidgetInterface
    {
        $this->priority = $priority;

        return $this;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }

    public function __invoke(?string $template = null): string
    {
        if ($template === null) {
            $template = $this->template;
        }

        if ($this->hasTrait(TwigAware::class)) {
            $output = $this->twig->render($template, $this->context);
        } else {
            $output = $template;
        }

        return sprintf(
            '<div id="widget-%s" name="%s">%s</div>',
            $this->getSlug(),
            $this->getName(),
            $output
        );
    }

    public function setTemplate(string $template): WidgetInterface
    {
        $this->template = $template;

        return $this;
    }

    public function getTemplate(): string
    {
        return $this->template;
    }

    public function setResponse(?Response $response = null): WidgetInterface
    {
        if ($response !== null) {
            $this->response = $response;
        }

        return $this;
    }

    public function getResponse(): ?Response
    {
        return $this->response;
    }

    public function setZone(string $zone): WidgetInterface
    {
        $this->zone = $zone;

        return $this;
    }

    public function getZone(): string
    {
        return $this->zone;
    }

    public function getSlug(): string
    {
        if ($this->slug === null) {
            $slugify = Slugify::create();
            $this->slug = $slugify->slugify($this->name);
        }

        return $this->slug;
    }

    public function hasTrait(string $classname)
    {
        return in_array($classname, $this->getTraits(), true);
    }

    /**
     * Get all `class_uses` traits from current class, as well as from its
     * parent classes and traits.
     */
    private function getTraits(): array
    {
        $class = $this;
        $traits = [];

        do {
            $traits = array_merge(class_uses($class), $traits);
            $class = get_parent_class($class);
        } while ($class);

        foreach (array_keys($traits) as $trait) {
            $traits = array_merge(class_uses($trait), $traits);
        }

        return array_unique($traits);
    }
}
