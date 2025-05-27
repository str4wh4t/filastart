<?php

namespace App\Filament\Resources\Blog\CategoryResource\Pages;

use App\Filament\Resources\Blog\CategoryResource;
use App\Filament\Resources\Blog\CategoryResource\Widgets\CategoryDistributionWidget;
use App\Filament\Resources\Blog\PostResource;
use App\Models\Blog\Category;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Filament\Tables;
use Filament\Forms;
use Filament\Tables\Filters\Filter;
use Livewire\Attributes\Url;

class ListCategories extends ListRecords
{
    protected static string $resource = CategoryResource::class;

    #[Url]
    public ?string $activeTab = null;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Create Record')
                ->color('primary')
                ->icon('heroicon-o-plus-circle'),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [];
    }

    protected function getFooterWidgets(): array
    {
        return [
            // CategoryDistributionWidget::class,
        ];
    }

    public function getTitle(): string
    {
        return __('Blog Categories');
    }

    public function getTabs(): array
    {
        $tabs = [
            'all' => Tab::make()->modifyQueryUsing(function (Builder $query) {
                /** @var \App\Models\Category $query */
                $query->withoutTrashed();
            }),
            'trashed' => Tab::make()->modifyQueryUsing(function (Builder $query) { 
                /** @var \App\Models\Category $query */
                $query->onlyTrashed();
            })->icon('heroicon-o-trash'),
        ];

        return $tabs;
    }

    public function updatedActiveTab(): void
    {
        $this->resetPage();
        $this->deselectAllTableRecords();
    }

    protected function getTableActions(): array
    {
        return [
            Tables\Actions\ViewAction::make()
                    ->hiddenLabel()
                    ->tooltip('View')
                    ->extraModalFooterActions(
                        [
                            Tables\Actions\EditAction::make()
                                ->visible(fn(Category $category): bool => !$category->trashed()),
                            Tables\Actions\Action::make('view_posts')
                                ->icon('fluentui-news-20')
                                ->color('success')
                                ->url(fn(Category $category): string => PostResource::getUrl('index', [
                                    'tableFilters[blog_category_id][value]' => $category->id
                                ])),
                        ]),
                // Tables\Actions\EditAction::make()->hiddenLabel()->tooltip('Edit')->hidden(fn(Category $category): bool => $category->trashed()),
                Tables\Actions\Action::make('view_posts')
                    ->hiddenLabel()
                    ->tooltip('View Posts')
                    ->icon('fluentui-news-20')
                    ->color('success')
                    ->url(fn(Category $record): string => PostResource::getUrl('index', [
                        'tableFilters[blog_category_id][value]' => $record->id,
                    ])),
        ];
    }
}
