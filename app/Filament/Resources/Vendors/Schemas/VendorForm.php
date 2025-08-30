<?php

namespace App\Filament\Resources\Vendors\Schemas;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use App\Enums\VendorStatusEnum;

class VendorForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('filament.sections.vendor_info'))
                    ->schema([
                        TextInput::make('name')
                            ->label(__('filament.fields.name'))
                            ->required()
                            ->columnSpan(1),

                        TextInput::make('email')
                            ->label(__('filament.fields.email'))
                            ->email()
                            ->required()
                            ->columnSpan(1),

                        TextInput::make('phone')
                            ->label(__('filament.fields.phone'))
                            ->required()
                            ->columnSpan(1),

                        Select::make('status')
                            ->label(__('filament.fields.status'))
                            ->options([
                                VendorStatusEnum::PENDING->value  => VendorStatusEnum::PENDING->label(),
                                VendorStatusEnum::APPROVED->value => VendorStatusEnum::APPROVED->label(),
                                VendorStatusEnum::REJECTED->value => VendorStatusEnum::REJECTED->label(),
                            ])
                            ->required()
                            ->live()
                            ->columnSpan(1),

                        Textarea::make('rejected_reason')
                            ->label(__('filament.fields.rejected_reason'))
                            ->visible(fn ($get) => $get('status') === VendorStatusEnum::REJECTED->value)
                            ->requiredIf('status', VendorStatusEnum::REJECTED->value)
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }
}
