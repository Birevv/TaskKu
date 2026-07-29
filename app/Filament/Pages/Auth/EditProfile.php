<?php

namespace App\Filament\Pages\Auth;

use Filament\Actions\Action;
use Filament\Auth\Pages\EditProfile as BaseEditProfile;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;

class EditProfile extends BaseEditProfile
{
    public function getHeading(): string|Htmlable|null
    {
        return 'Profile settings';
    }

    public function getSubheading(): string|Htmlable|null
    {
        return 'Keep your personal details, profile photo, and account security up to date.';
    }

    public function getMaxWidth(): Width|string|null
    {
        return Width::SevenExtraLarge;
    }

    /**
     * @return array<string>
     */
    public function getPageClasses(): array
    {
        return ['taskku-profile-page'];
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->inlineLabel(false)
            ->components([
                Grid::make([
                    'default' => 1,
                    'lg' => 3,
                ])
                    ->schema([
                        Section::make('Profile photo')
                            ->description('A clear photo helps your teammates recognize you.')
                            ->icon(Heroicon::OutlinedCamera)
                            ->iconColor('primary')
                            ->schema([
                                FileUpload::make('avatar_path')
                                    ->label('Photo')
                                    ->helperText('JPG or PNG, up to 2 MB.')
                                    ->avatar()
                                    ->imageEditor()
                                    ->imagePreviewHeight('8rem')
                                    ->disk('public')
                                    ->directory('avatars')
                                    ->visibility('public')
                                    ->maxSize(2048),
                            ])
                            ->extraAttributes(['class' => 'taskku-profile-avatar-card'])
                            ->columnSpan([
                                'default' => 1,
                                'lg' => 1,
                            ]),
                        Group::make([
                            Section::make('Personal information')
                                ->description('This information identifies your Taskku account.')
                                ->icon(Heroicon::OutlinedIdentification)
                                ->schema([
                                    $this->getNameFormComponent(),
                                    $this->getEmailFormComponent(),
                                ])
                                ->columns([
                                    'default' => 1,
                                    'md' => 2,
                                ])
                                ->extraAttributes(['class' => 'taskku-profile-details-card']),
                            Section::make('Password')
                                ->description('Leave the new password fields empty to keep your current password.')
                                ->icon(Heroicon::OutlinedShieldCheck)
                                ->schema([
                                    $this->getPasswordFormComponent(),
                                    $this->getPasswordConfirmationFormComponent(),
                                    $this->getCurrentPasswordFormComponent()
                                        ->columnSpanFull(),
                                ])
                                ->columns([
                                    'default' => 1,
                                    'md' => 2,
                                ])
                                ->extraAttributes(['class' => 'taskku-profile-security-card']),
                        ])
                            ->extraAttributes(['class' => 'taskku-profile-fields'])
                            ->columnSpan([
                                'default' => 1,
                                'lg' => 2,
                            ]),
                    ])
                    ->extraAttributes(['class' => 'taskku-profile-grid']),
            ]);
    }

    public function getFormContentComponent(): Component
    {
        return parent::getFormContentComponent()
            ->extraAttributes(['class' => 'taskku-profile-form']);
    }

    public function getMultiFactorAuthenticationContentComponent(): ?Component
    {
        return parent::getMultiFactorAuthenticationContentComponent()
            ?->extraAttributes(['class' => 'taskku-profile-mfa']);
    }

    protected function getSaveFormAction(): Action
    {
        return parent::getSaveFormAction()
            ->label('Save changes')
            ->icon(Heroicon::OutlinedCheck);
    }
}
