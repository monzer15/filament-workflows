<?php

namespace Monzer\FilamentWorkflows;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Filament\Support\Concerns\EvaluatesClosures;
use Monzer\FilamentWorkflows\Resources\WorkflowResource;

class WorkflowsPlugin implements Plugin
{
    use EvaluatesClosures;

    protected bool|\Closure $authorizeUsing = true;

    protected array $actions = [];
    protected int $navigation_sort = 100;
    protected ?string $navigation_group = null;
    protected string $navigation_icon = "heroicon-o-rectangle-stack";

    protected string $slug = "workflows";

    protected bool $should_register_navigation = true;

    protected bool $enable_testing = false;

    public function actions(array $actions): static
    {
        $this->actions = $actions;
        return $this;
    }

    public function slug(string $slug): static
    {
        $this->slug = $slug;
        return $this;
    }

    public function navigationIcon(string $icon): static
    {
        $this->navigation_icon = $icon;
        return $this;
    }

    public function navigationSort(int $sort): static
    {
        $this->navigation_sort = $sort;
        return $this;
    }

    public function navigationGroup(?string $group): static
    {
        $this->navigation_group = $group;
        return $this;
    }

    public function shouldRegisterNavigation(bool $shouldRegisterNavigation = true): static
    {
        $this->should_register_navigation = $shouldRegisterNavigation;
        return $this;
    }

    public function enableTesting(bool $enable_testing = true): static
    {
        $this->enable_testing = $enable_testing;
        return $this;
    }

    public function getActions(): array
    {
        return array_unique(array_merge(config('workflows.actions', []), $this->actions));
    }

    public function getNavigationSort(): int
    {
        return $this->navigation_sort;
    }

    public function getNavigationIcon(): string
    {
        return $this->navigation_icon;
    }

    public function getNavigationGroup(): ?string
    {
        return $this->navigation_group;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function getShouldRegisterNavigation(): bool
    {
        return $this->should_register_navigation;
    }

    public function isTestingEnabled(): bool
    {
        return $this->enable_testing;
    }

    public static function make(): static
    {
        return app(static::class);
    }

    public function getId(): string
    {
        return 'filament-workflows';
    }

    public function register(Panel $panel): void
    {
        $panel
            ->resources([
                WorkflowResource::class,
            ]);
    }

    public function boot(Panel $panel): void
    {
        // TODO: Implement boot() method.
    }

    public function authorize(bool|\Closure $callback = true): static
    {
        $this->authorizeUsing = $callback;

        return $this;
    }

    public function isAuthorized(): bool
    {
        return $this->evaluate($this->authorizeUsing) === true;
    }
}
