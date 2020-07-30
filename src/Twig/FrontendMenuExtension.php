<?php

declare(strict_types=1);

namespace Bolt\Twig;

use Bolt\Menu\FrontendMenuBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class FrontendMenuExtension extends AbstractExtension
{
    /** @var FrontendMenuBuilderInterface */
    private $menuBuilder;

    /** @var Request */
    private $request;

    public function __construct(FrontendMenuBuilderInterface $menuBuilder, RequestStack $requestStack)
    {
        $this->menuBuilder = $menuBuilder;
        $this->request = $requestStack->getCurrentRequest();
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions(): array
    {
        $safe = [
            'is_safe' => ['html'],
        ];
        $env = ['needs_environment' => true];

        return [
            new TwigFunction('menu', [$this, 'renderMenu'], $env + $safe),
            new TwigFunction('menu_array', [$this, 'getMenu'], $safe),
        ];
    }

    public function getMenu(?string $name = null): array
    {
        return $this->menuBuilder->buildMenu($name);
    }

    public function renderMenu(Environment $twig, ?string $name = null, string $template = 'helpers/_menu.html.twig', string $class = '', bool $withsubmenus = true): string
    {
        $context = [
            'menu' => $this->menuBuilder->buildMenu($name),
            'class' => $class,
            'withsubmenus' => $withsubmenus,
        ];

        return $twig->render($template, $context);
    }

    public function isCurrent($item): bool
    {
        $uri = $item['uri'] ?? '';

        return $uri === $this->request->getRequestUri();
    }
}
