<?php

namespace App\Filament\Resources\Projects\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class ProjectsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ColorColumn::make('color')
                    ->label('Color'),

                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('visibility')
                    ->badge(),

                TextColumn::make('tasks_count')
                    ->counts('tasks')
                    ->label('Tasks'),

                TextColumn::make('created_at')
                    ->dateTime('d M Y')
                    ->sortable(),
            ])

            ->filters([
                TrashedFilter::make(),
            ])

            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
                RestoreAction::make(),
                ForceDeleteAction::make(),
            ])

            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
