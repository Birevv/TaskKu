<?php

namespace App\Filament\Pages;

use App\Models\Task;
use App\Models\Workspace;
use Filament\Facades\Filament;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use BackedEnum;
use UnitEnum;

class UpcomingTasks extends Page implements HasTable
{
    use InteractsWithTable;

    protected string $view = 'filament.pages.upcoming-tasks';

    protected static ?string $navigationLabel = 'Upcoming';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-calendar-days';

    protected static string|UnitEnum|null $navigationGroup = 'Workspace';

    public function table(Table $table) : Table
    {
        $workspace = Filament::getTenant();

        $query = Task::query()
            ->whereNull('archived_at')
            ->whereNull('deleted_at')
            ->where('due_at','>=', now())
            ->orderBy('due_at');

        if($workspace instanceof Workspace) {
            $query->whereBelongsTo($workspace);
        }

        return $table
            ->query($query)
            ->columns([
                TextColumn::make('title')
                    ->label('Task')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('project.name')
                    ->label('Project')
                    ->sortable(),

                TextColumn::make('priority')
                    ->badge(),

                TextColumn::make('status')
                    ->badge(),

                TextColumn::make('due_at')
                    ->label('Due Date')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
            ])
            ->defaultSort('due_at', 'asc');
    }
}
