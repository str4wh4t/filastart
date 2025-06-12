<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UnitResource\Pages;
use App\Filament\Resources\UnitResource\RelationManagers;
use App\Models\Unit;
use Closure;
use Dom\Text;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;

class UnitResource extends Resource
{
    protected static bool $shouldRegisterNavigation = false; // hide from sidebar
    protected static ?string $model = Unit::class;

    protected static ?string $navigationGroup = 'Organization';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?int $navigationSort = 0;
    protected static ?string $navigationLabel = 'Unit List';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // buatkan form untuk Unit sesuai dengan field yang ada di model Unit
                Forms\Components\TextInput::make('name')
                    ->label('Unit Name')
                    ->required()
                    ->maxLength(255),  
                // Forms\Components\Hidden::make('code'),
                Forms\Components\TextInput::make('code_temp')
                    ->label('Unit Code')
                    ->required()
                    ->prefix(function ($get) {
                        $parentId = $get('parent_id');
                        if ($parentId) {
                            $parent = \App\Models\Unit::find($parentId);
                            return $parent ? $parent->code : '';
                        }
                        return '';
                    })
                    ->rules([
                        fn ($set, $get, $record): Closure => function (string $attribute, $value, Closure $fail) use ($set, $get, $record) {
                            // dd($get('code_temp'));
                            $code = $get('code_temp');
                            $parentId = $get('parent_id');
                            if ($parentId) {
                                $parent = \App\Models\Unit::find($parentId);
                                $code = $parent ? $parent->code . $code : $code;
                            }
                            // dd($code);
                            $existingCode = \App\Models\Unit::where('code', $code )
                                ->when($record, function (Builder $query) use ($record) {
                                    return $query->where('id', '!=', $record->id);
                                })
                                ->exists();
                            // dd($existingCode);
                            if ($existingCode) {
                                $fail('The :attribute has already been taken.');
                            }
                        },
                    ]),
                Forms\Components\Select::make('parent_id')
                    ->label('Parent Unit')
                    ->relationship(
                        'parent', 
                        'name',
                        fn ($query, $record) => $record ? $query->where('id', '!=', $record->id)->orderBy('code') : $query
                    )
                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->code} | {$record->name}")
                    ->nullable()
                    ->searchable()
                    ->preload()
                    ->reactive()
                    ->createOptionForm([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('code')
                            ->required()
                            ->maxLength(50),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('code')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Unit Name')
                    ->description(fn(Unit $record): string => $record->parent ? "Child of : {$record->parent->name}" : '')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('code')
                    ->label('Unit Code')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('parent.name')
                    ->label('Parent Unit')
                    ->searchable()
                    ->sortable(),
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
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
            'index' => Pages\ListUnits::route('/'),
            'create' => Pages\CreateUnit::route('/create'),
            'edit' => Pages\EditUnit::route('/{record}/edit'),
        ];
    }
}
