<?php

    namespace App\Filament\Resources\RentalShops\Schemas;

    use Filament\Schemas\Schema;
    use Filament\Schemas\Components\Section;
    use Filament\Forms\Components\TextInput;
    use Filament\Forms\Components\Select;
    use Filament\Forms\Components\Textarea;
    use Filament\Forms\Components\FileUpload;
    use Filament\Forms\Components\Toggle;
    use App\Enums\RentalShopStatusEnum;

    class RentalShopForm
    {
        public static function configure(Schema $schema): Schema
        {
            return $schema
                ->components([
                    Section::make(__('filament.sections.rental_shop_info'))
                        ->schema([
                            TextInput::make('name')
                                ->label(__('filament.fields.name'))
                                ->required()
                                ->columnSpan(1),

                            TextInput::make('phone')
                                ->label(__('filament.fields.phone'))
                                ->required()
                                ->columnSpan(1),

                            Toggle::make('is_active')
                                ->label(__('filament.fields.active'))
                                ->default(true)
                                ->columnSpan(1),

                            Select::make('status')
                                ->label(__('filament.fields.status'))
                                ->options([
                                    RentalShopStatusEnum::PENDING->value  => RentalShopStatusEnum::PENDING->label(),
                                    RentalShopStatusEnum::APPROVED->value => RentalShopStatusEnum::APPROVED->label(),
                                    RentalShopStatusEnum::REJECTED->value => RentalShopStatusEnum::REJECTED->label(),
                                ])
                                ->required()
                                ->live()
                                ->columnSpan(1),

                            Textarea::make('rejected_reason')
                                ->label(__('filament.fields.rejected_reason'))
                                ->visible(fn ($get) => $get('status') === RentalShopStatusEnum::REJECTED->value)
                                ->requiredIf('status', RentalShopStatusEnum::REJECTED->value)
                                ->columnSpanFull(),

                            FileUpload::make('image')
                                ->label(__('filament.fields.image'))
                                ->image()
                                ->disk('public')
                                ->directory('rental_shops/images')
                                ->columnSpan(1),

                            FileUpload::make('transport_license_photo')
                                ->label(__('filament.fields.transport_license_photo'))
                                ->image()
                                ->disk('public')
                                ->directory('rental_shops/licenses')
                                ->columnSpan(1),

                            FileUpload::make('commerical_registration_photo')
                                ->label(__('filament.fields.commerical_registration_photo'))
                                ->image()
                                ->disk('public')
                                ->directory('rental_shops/registrations')
                                ->columnSpan(1),
                        ])
                        ->columns(2)
                        ->columnSpanFull(),
                ]);
        }
    }
