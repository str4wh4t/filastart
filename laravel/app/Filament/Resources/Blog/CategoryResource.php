<?php

namespace App\Filament\Resources\Blog;

use App\Filament\Resources\Blog\CategoryResource\Pages;
use App\Models\Blog\Category;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms;
use Filament\Infolists;
use Filament\Forms\Form;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Filament\Forms\Components\Tabs;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

class CategoryResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = Category::class;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $slug = 'blog/categories';

    protected static ?int $navigationSort = -1;
    protected static ?string $navigationIcon = 'fluentui-stack-20';

    public static function getPermissionPrefixes(): array
    {
        return [
            'view',
            'view_any',
            'create',
            'update',
            'restore',
            'restore_any',
            'delete',
            'delete_any',
            'force_delete',
            'force_delete_any',
        ];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('Category Details')
                    ->tabs([
                        Tabs\Tab::make('Basic Information')
                            ->schema([
                                Forms\Components\Select::make('parent_id')
                                    ->label('Parent Category')
                                    ->options(function () {
                                        // Exclude the current category if editing
                                        $query = Category::query();
                                        if (request()->route('record')) {
                                            $query->where('id', '!=', request()->route('record'));
                                        }
                                        return $query->pluck('name', 'id');
                                    })
                                    ->searchable()
                                    ->nullable()
                                    ->preload()
                                    ->columnSpan('full'),

                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->maxLength(255)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn(string $operation, $state, Forms\Set $set) =>
                                        $operation === 'create' ? $set('slug', Str::slug($state)) : null),

                                Forms\Components\TextInput::make('slug')
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(Category::class, 'slug', ignoreRecord: true)
                                    ->helperText('URL-friendly name. Will be auto-generated from the name if left empty.'),

                                Forms\Components\Select::make('locale')
                                    ->options([
                                        'en' => 'English',
                                        'id' => 'Indonesian',
                                        'zh' => 'Chinese',
                                        'ja' => 'Japanese',
                                        // Add more languages as needed
                                    ])
                                    ->default('en')
                                    ->required(),

                                Forms\Components\Toggle::make('is_active')
                                    ->label('Active')
                                    ->helperText('Only active categories will be shown on the frontend')
                                    ->default(true),
                            ]),

                        Tabs\Tab::make('Content')
                            ->schema([
                                Forms\Components\MarkdownEditor::make('description')
                                    ->columnSpan('full')
                                    ->fileAttachmentsDisk('public')
                                    ->fileAttachmentsDirectory('category-images')
                                    ->fileAttachmentsVisibility('public'),
                            ]),

                        Tabs\Tab::make('SEO & Meta')
                            ->schema([
                                Forms\Components\TextInput::make('meta_title')
                                    ->maxLength(255)
                                    ->helperText('Leave blank to use the category name'),

                                Forms\Components\Textarea::make('meta_description')
                                    ->maxLength(500)
                                    ->rows(3)
                                    ->helperText('Brief description for search engines. Recommended length: 150-160 characters.'),
                            ]),

                        Tabs\Tab::make('Advanced Options')
                            ->schema([
                                Forms\Components\KeyValue::make('options')
                                    ->keyLabel('Option Name')
                                    ->valueLabel('Option Value')
                                    ->addable()
                                    ->reorderable()
                                    ->columnSpan('full')
                                    ->helperText('Custom options for this category (JSON format)')
                            ]),
                    ])
                    ->columnSpan('full'),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Basic Information')
                    ->schema([
                        Infolists\Components\TextEntry::make('name')
                            ->size(Infolists\Components\TextEntry\TextEntrySize::Large),
                        Infolists\Components\TextEntry::make('slug')
                            ->label('Slug (URL)'),
                        Infolists\Components\TextEntry::make('parent.name')
                            ->label('Parent Category')
                            ->default('None'),
                        Infolists\Components\IconEntry::make('is_active')
                            ->label('Status')
                            ->boolean(),
                        Infolists\Components\TextEntry::make('locale')
                            ->label('Language'),
                    ])
                    ->columns(2),

                Infolists\Components\Section::make('Description')
                    ->schema([
                        Infolists\Components\TextEntry::make('description')
                            ->markdown(),
                    ]),

                Infolists\Components\Section::make('SEO Information')
                    ->schema([
                        Infolists\Components\TextEntry::make('meta_title'),
                        Infolists\Components\TextEntry::make('meta_description'),
                    ])
                    ->columns(2),

                Infolists\Components\Section::make('Statistics')
                    ->schema([
                        Infolists\Components\TextEntry::make('posts_count')
                            ->label('Number of Posts')
                            ->state(fn(Category $record): int => $record->posts()->count()),
                        Infolists\Components\TextEntry::make('created_at')
                            ->dateTime(),
                        Infolists\Components\TextEntry::make('updated_at')
                            ->dateTime(),
                        Infolists\Components\TextEntry::make('creator.name')
                            ->label('Created By')
                            ->default('System'),
                        Infolists\Components\TextEntry::make('updater.name')
                            ->label('Last Updated By')
                            ->default('System'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->description(fn(Category $record) => $record->parent ? "Child of {$record->parent->name}" : '')
                    ->wrap(),
                Tables\Columns\TextColumn::make('slug')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('creator.name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('locale')
                    ->badge()
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('posts_count')
                    ->label('Posts')
                    ->counts('posts')
                    ->sortable()
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('parent_id')
                    ->label('Parent Category')
                    ->options(fn() => Category::pluck('name', 'id'))
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('locale')
                    ->options([
                        'en' => 'English',
                        'id' => 'Indonesian',
                        'zh' => 'Chinese',
                        'ja' => 'Japanese',
                    ]),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Status'),
                Tables\Filters\TernaryFilter::make('root')
                    ->label('Root Categories Only')
                    ->queries(
                        true: fn(Builder $query) => $query->whereNull('parent_id'),
                        false: fn(Builder $query) => $query->whereNotNull('parent_id'),
                    ),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->hiddenLabel()
                    ->tooltip('View')
                    ->extraModalFooterActions(
                        [
                            Tables\Actions\EditAction::make()
                                ->url(fn(Category $category, $livewire): string => CategoryResource::getUrl('edit', ['record' => $category, 'page' => $livewire->getPage(), 'activeTab' => $livewire->activeTab, 'tableFilters' => $livewire->tableFilters]))
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
                    ->url(fn(Category $category): string => PostResource::getUrl('index', [
                        'tableFilters[blog_category_id][value]' => $category->id,
                    ])),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->label('Trash selected')
                        ->visible(fn ($livewire): bool => $livewire->activeTab !== 'trashed'),
                    Tables\Actions\RestoreBulkAction::make()
                        ->color('success')
                        ->visible(fn ($livewire): bool => $livewire->activeTab === 'trashed'),
                    Tables\Actions\ForceDeleteBulkAction::make()
                        ->visible(fn ($livewire): bool => $livewire->activeTab === 'trashed'),
                    Tables\Actions\BulkAction::make('activate')
                        ->label('Set Active')
                        ->icon('heroicon-m-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(fn(Collection $records) => $records->each->update(['is_active' => true]))
                        ->visible(fn ($livewire): bool => $livewire->activeTab !== 'trashed'),
                    Tables\Actions\BulkAction::make('deactivate')
                        ->label('Set Inactive')
                        ->icon('heroicon-m-x-circle')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->action(fn(Collection $records) => $records->each->update(['is_active' => false]))
                        ->visible(fn ($livewire): bool => $livewire->activeTab !== 'trashed'),
                ]),
            ])
            ->defaultSort('updated_at', 'desc')
            ->checkIfRecordIsSelectableUsing(
                function(Category $category): bool{
                    $user = Auth::user();
                    if ($user->hasAnyRole(['admin', config('filament-shield.super_admin.name')])) {
                        return true;
                    }
                    return $category->created_by == $user->id;
                }
            )
            ->recordUrl(null);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
        ];
    }

    public static function getNavigationGroup(): ?string
    {
        return __("menu.nav_group.blog");
    }
}
