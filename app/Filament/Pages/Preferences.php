<?php

namespace App\Filament\Pages;

use App\Enums\DisplayDensity;
use App\Models\User;
use App\Models\UserSettings;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class Preferences extends Page
{
    protected string $view = 'filament.pages.preferences';

    protected static ?string $navigationLabel = 'Preferences';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static string|UnitEnum|null $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 1;

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill(
            $this->getRecord()->attributesToArray(),
        );
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Form::make([
                    Section::make('Appearance')
                        ->schema([
                            Select::make('theme')
                                ->label('Theme')
                                ->options([
                                    'system' => 'System',
                                    'light' => 'Light',
                                    'dark' => 'Dark',
                                ])
                                ->required(),

                            Select::make('density')
                                ->label('Density')
                                ->options(DisplayDensity::class)
                                ->default(DisplayDensity::Comfortable)
                                ->required(),
                        ]),

                    Section::make('Notifications')
                        ->schema([
                            Toggle::make('notify_task_assigned')
                                ->label('Task assigned to me'),

                            Toggle::make('notify_task_due')
                                ->label('Task due reminder'),
                        ]),
                ])
                    ->livewireSubmitHandler('save')
                    ->footer([
                        Actions::make([
                            Action::make('save')
                                ->label('Save change')
                                ->submit('save')
                                ->keyBindings(['mods+s']),
                        ]),
                    ]),
            ])
            ->record($this->getRecord())
            ->statePath('data');
    }

    public function save(): void
    {
        $state = $this->form->getState();

        $this->getRecord()->update($state);

        $this->dispatch(
            'taskku-theme-updated',
            theme: $state['theme'],
        );

        $this->dispatch(
            'taskku-density-updated',
            density: $state['density'],
        );

        Notification::make()
            ->success()
            ->title('Preferences Saved')
            ->send();
    }

    public function getRecord(): UserSettings
    {
        $user = Auth::user();

        if (! $user instanceof User) {
            throw new AuthenticationException;
        }

        return $user->settings()->firstOrCreate();
    }
}
