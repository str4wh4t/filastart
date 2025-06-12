<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use App\Settings\MailSettings;
use Closure;
use Exception;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Form;
use Filament\Infolists\Infolist;
use Filament\Notifications\Auth\VerifyEmail;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use STS\FilamentImpersonate\Tables\Actions\Impersonate;
use Filament\Infolists;
use Filament\Infolists\Components\SpatieMediaLibraryImageEntry;

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static int $globalSearchResultsLimit = 20;

    protected static ?int $navigationSort = 0;
    protected static ?string $navigationIcon = 'fluentui-people-team-20-o';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        SpatieMediaLibraryFileUpload::make('media')
                            ->hiddenLabel()
                            ->avatar()
                            ->collection('avatars')
                            ->alignCenter()
                            ->uploadingMessage('Uploading...')
                            ->columnSpanFull(),

                        Forms\Components\Actions::make([
                            Action::make('resend_verification')
                                ->label(__('resource.user.actions.resend_verification'))
                                ->color('info')
                                ->action(fn(MailSettings $settings, Model $record) => static::doResendEmailVerification($settings, $record)),
                        ])
                            ->hidden(fn (User $user, string $operation): bool => $user->email_verified_at !== null || $operation === 'create')
                            ->fullWidth(),

                            Forms\Components\Section::make()
                                ->schema([
                                    Forms\Components\TextInput::make('password')
                                        ->password()
                                        ->dehydrateStateUsing(fn(string $state): string => Hash::make($state))
                                        ->dehydrated(fn(?string $state): bool => filled($state))
                                        ->revealable()
                                        ->required()
                                        ->default(env('DEFAULT_USER_PASSWORD', '12345678')),
                                    Forms\Components\TextInput::make('passwordConfirmation')
                                        ->password()
                                        ->dehydrateStateUsing(fn(string $state): string => Hash::make($state))
                                        ->dehydrated(fn(?string $state): bool => filled($state))
                                        ->revealable()
                                        ->same('password')
                                        ->required()
                                        ->default(env('DEFAULT_USER_PASSWORD', '12345678')),
                                ])
                                ->compact()
                                ->hidden(fn(string $operation): bool => $operation === 'edit'),

                        Forms\Components\Section::make()
                            ->schema([
                                Forms\Components\Placeholder::make('email_verified_at')
                                    ->label(__('resource.general.email_verified_at'))
                                    ->content(fn(User $record): ?HtmlString => $record->email_verified_at !== null ? new HtmlString("<span class='text-teal-500'>".$record->email_verified_at->format('M j, Y H:i')."</span>") : new HtmlString("<span class='inline-flex items-center px-2 py-1 text-xs font-semibold rounded-md text-danger-700 bg-danger-50 ring-1 ring-inset ring-danger-600/20'>Unverified</span>")),
                                Forms\Components\Placeholder::make('created_at')
                                    ->label(__('resource.general.created_at'))
                                    ->content(fn(User $record): ?string => $record->created_at?->diffForHumans()),
                                Forms\Components\Placeholder::make('updated_at')
                                    ->label(__('resource.general.updated_at'))
                                    ->content(fn(User $record): ?string => $record->updated_at?->diffForHumans()),
                            ])
                            ->compact()
                            ->hidden(fn(string $operation): bool => $operation === 'create'),
                    ])
                    ->columnSpan(1),

                Forms\Components\Tabs::make()
                    ->schema([
                        Forms\Components\Tabs\Tab::make('Details')
                            ->icon('heroicon-o-information-circle')
                            ->schema([
                                Forms\Components\TextInput::make('username')
                                    ->required()
                                    ->maxLength(255)
                                    ->live()
                                    ->rules(function ($record) {
                                        $userId = $record?->id;
                                        return $userId
                                            ? ['unique:users,username,' . $userId]
                                            : ['unique:users,username'];
                                    }),

                                Forms\Components\TextInput::make('email')
                                    ->email()
                                    ->required()
                                    ->maxLength(255)
                                    ->rules(function ($record) {
                                        $userId = $record?->id;
                                        return $userId
                                            ? ['unique:users,email,' . $userId]
                                            : ['unique:users,email'];
                                    }),

                                Forms\Components\TextInput::make('firstname')
                                    ->required()
                                    ->maxLength(255),

                                Forms\Components\TextInput::make('lastname')
                                    ->required()
                                    ->maxLength(255),
                            ])
                            ->columns(2),

                        Forms\Components\Tabs\Tab::make('Roles')
                            ->icon('fluentui-shield-task-48')
                            ->schema([
                                // Select::make('roles')
                                    // ->hiddenLabel()
                                    // ->relationship('roles', 'name')
                                    // ->getOptionLabelFromRecordUsing(fn(Model $record) => Str::headline($record->name))
                                    // ->multiple()
                                    // ->preload()
                                    // ->searchable()
                                    // ->optionsLimit(5)
                                    // ->columnSpanFull(),
                                Forms\Components\CheckboxList::make('roles')
                                    ->label('')
                                    ->columns(2)
                                    ->relationship(
                                        'roles',
                                        'name',
                                        function ($query) {
                                            /** @var \App\Models\User $user */
                                            $user = Auth::user();
                                            return $user->isSuperAdmin()
                                                ? $query->whereNotIn('name', [config('filament-shield.super_admin.name')])
                                                : $query->whereNotIn('name', [config('filament-shield.super_admin.name'), 'admin']);
                                        }
                                    )
                                     ->rules([
                                        fn (?User $record, string $context): Closure => function (string $attribute, $value, Closure $fail) use ($record, $context) {

                                            if ($context !== 'edit') {
                                                return;
                                            }

                                            /** @var \App\Models\User $currentUser */
                                            $currentUser = Auth::user();
                                            // $record = $this->record; // pastikan ini bisa diakses
                                            
                                            $wasAdminBefore = $record?->hasRole('admin');

                                            // If the user is already admin, inject '2' into the roles array
                                            if($wasAdminBefore) {
                                                array_push($value, '2');
                                            }   

                                            $isTryingToAssignAdmin = in_array('2', $value);

                                            // 1. Tidak boleh mencabut role admin jika bukan super_admin
                                            if (!$currentUser->isSuperAdmin()) {
                                                // Kasus: role admin dicabut
                                                if ($wasAdminBefore && !$isTryingToAssignAdmin) {
                                                    $fail('You are not allowed to remove the admin role.');
                                                }
                                    
                                                // Kasus: role admin diberikan
                                                if (!$wasAdminBefore && $isTryingToAssignAdmin) {
                                                    $fail('You are not allowed to assign the admin role.');
                                                }
                                            }
                                    
                                            // 2. Siapa pun tidak boleh mengubah role super_admin (lebih ketat)
                                            if (in_array('1', $value) || $record?->isSuperAdmin()) {
                                                if (!$currentUser->isSuperAdmin()) {
                                                    $fail('Only super admin can manage the super admin role.');
                                                }
                                            }
                                        },
                                    ])
                                    ->columnSpanFull(),
                            ])
                    ])
                    ->columnSpan([
                        'sm' => 1,
                        'lg' => 2
                    ]),
            ])
            ->columns(3);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('User Details')
                    ->schema([
                        SpatieMediaLibraryImageEntry::make('media')
                            ->hiddenLabel()
                            ->collection('avatars')
                            ->circular()
                            ->defaultImageUrl(fn(User $record): ?string => $record->getFilamentAvatarUrl()),
                        Infolists\Components\TextEntry::make('name'),
                        Infolists\Components\TextEntry::make('username'),
                        Infolists\Components\TextEntry::make('email'),
                        Infolists\Components\TextEntry::make('verified_status')
                            ->color(fn (string $state): string => match ($state) {
                                'Verified' => 'success',
                                'Unverified' => 'warning',
                            })
                            ->badge(),
                        Infolists\Components\TextEntry::make('email_verified_at')->dateTime(),
                        Infolists\Components\TextEntry::make('created_at')->dateTime(),
                        Infolists\Components\TextEntry::make('updated_at')->dateTime(),
                    ])->columns(2),
                Infolists\Components\Section::make('Roles')
                    ->schema([
                        Infolists\Components\TextEntry::make('roles.name')
                            ->formatStateUsing(fn($state): string => Str::headline($state))
                            ->badge(),
                    ])
                    ->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('media')
                    ->label('Avatar')
                    ->collection('avatars')
                    ->wrap()
                    ->defaultImageUrl(fn(User $record): ?string => $record->getFilamentAvatarUrl()),
                Tables\Columns\TextColumn::make('username')->label('Username')
                    ->description(fn(User $record) => $record->firstname . ' ' . $record->lastname)
                    ->searchable(['username', 'firstname', 'lastname']),
                Tables\Columns\TextColumn::make('roles.name')->label('Role')
                    ->formatStateUsing(fn($state): string => Str::headline($state))
                    ->badge(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('verified_status')
                    ->label('Verified')
                    ->color(fn (string $state): string => match ($state) {
                        'Verified' => 'success',
                        'Unverified' => 'warning',
                    })
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email_verified_at')->label('Verified at')
                    ->dateTime('M j, Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created At')
                    ->date()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Last Update')
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role_id')
                    ->label('Role')
                    ->relationship('roles', 'name', function ($query) {
                        return $query->whereNotIn('name', [config('filament-shield.super_admin.name'), 'admin', 'author']);
                    })
                    ->searchable()
                    ->multiple()
                    ->preload(),
                Tables\Filters\TernaryFilter::make('email_verified_at')
                    ->label('Email verification')
                    ->placeholder('All users')
                    ->trueLabel('Verified users')
                    ->falseLabel('Unverified users')
                    ->queries(
                        true: fn (Builder $query) => $query->whereNotNull('email_verified_at'),
                        false: fn (Builder $query) => $query->whereNull('email_verified_at'),
                        blank: fn (Builder $query) => $query, // In this example, we do not want to filter the query when it is blank.
                    ),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->modal()
                    ->hiddenLabel()
                    ->extraModalFooterActions(
                        [
                            Tables\Actions\EditAction::make()
                                ->url(fn(User $user, $livewire): string => 
                                    UserResource::getUrl('edit', [
                                            'record' => $user, 
                                            'page' => $livewire->getPage(), 
                                            'activeTab' => $livewire->activeTab, 
                                            'tableFilters' => $livewire->tableFilters, 
                                            'tableSearch' => $livewire->tableSearch
                                        ]))
                                ->visible(fn(User $user): bool => !$user->trashed()),
                            Tables\Actions\DeleteAction::make(),
                            Impersonate::make()
                                ->button()
                                ->requiresConfirmation()
                                ->color('gray')
                                ->extraAttributes(['class' => 'fi-btn-ring-golden']),
                        ]),
                Tables\Actions\EditAction::make()
                    ->hiddenLabel()
                    ->url(fn(User $user, $livewire): string => 
                        UserResource::getUrl('edit', [
                            'record' => $user, 
                            'page' => $livewire->getPage(),
                            'activeTab' => $livewire->activeTab,
                            'tableFilters' => $livewire->tableFilters,
                            'tableSearch' => $livewire->tableSearch
                        ])),
                // Impersonate::make()->requiresConfirmation()->color('gray')
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function getNavigationGroup(): ?string
    {
        return __("menu.nav_group.access");
    }

    public static function doResendEmailVerification($settings = null, $user): void
    {
        if (!method_exists($user, 'notify')) {
            $userClass = $user::class;

            throw new Exception("Model [{$userClass}] does not have a [notify()] method.");
        }

        if ($settings->isMailSettingsConfigured()) {
            $notification = new VerifyEmail();
            $notification->url = Filament::getVerifyEmailUrl($user);

            $settings->loadMailSettingsToConfig();

            $user->notify($notification);


            Notification::make()
                ->title(__('resource.user.notifications.verify_sent.title'))
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title(__('resource.user.notifications.verify_warning.title'))
                ->body(__('resource.user.notifications.verify_warning.description'))
                ->warning()
                ->send();
        }
    }

    // public static function getGlobalSearchResultTitle(Model $record): string|Htmlable
    // {
    //     return $record->name . ' || ' . $record->username;
    // }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()
            ->whereDoesntHave('roles', function ($query) {
                $query->where('name', config('filament-shield.super_admin.name'));
            });
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['username', 'email', 'firstname', 'lastname'];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Username' => $record->username,
            'Email' => $record->email,
        ];
    }

    public static function getGlobalSearchResultUrl(Model $record): string
    {
        return UserResource::getUrl('index', ['tableSearch' => $record->name]);
    }
}
