<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LecturerResource\Pages;
use App\Models\Lecturer;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;

class LecturerResource extends Resource
{
    protected static ?string $model = Lecturer::class;
    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    protected static ?string $navigationLabel = 'Dosen';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nidn')
                    ->required()
                    ->numeric()
                    ->unique(ignoreRecord: true),
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true),
                Forms\Components\TextInput::make('password')
                    ->password()
                    ->required()
                    ->minLength(6)
                    ->visibleOn('create')
                    ->dehydrateStateUsing(function ($state) {
                        Log::info("Hashing password for lecturer creation", ['password_length' => strlen($state)]);
                        return Hash::make($state);
                    }),
                Forms\Components\Select::make('major')
                    ->required()
                    ->options([
                        'Informatika' => 'Informatika',
                        'Pertanian' => 'Pertanian',
                        'Sistem Informasi' => 'Sistem Informasi',
                        'Teknik Komputer' => 'Teknik Komputer',
                        'Biologi' => 'Biologi',
                        'Kedokteran' => 'Kedokteran',
                        'Ilmu Komunikasi' => 'Ilmu Komunikasi',
                        'Manajemen' => 'Manajemen',
                        'Film' => 'Film',
                        'DKV' => 'DKV',
                    ]),
                Forms\Components\TextInput::make('mata_kuliah')
                    ->required()
                    ->maxLength(255),
                Forms\Components\FileUpload::make('profile_photo')
                    ->image()
                    ->directory('profile-photos')
                    ->nullable()
                    ->dehydrateStateUsing(function ($state) {
                        Log::info("Processing profile_photo state", ['state' => $state]);
                        if (is_array($state)) {
                            return !empty($state) ? $state[0] : '/images/profile.jpg';
                        }
                        return $state ?? '/images/profile.jpg';
                    }),
                Forms\Components\TextInput::make('motto')
                    ->maxLength(255)
                    ->default('Veni, Vidi, Vici'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nidn')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('major')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('mata_kuliah')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLecturers::route('/'),
            'create' => Pages\CreateLecturer::route('/create'),
            'edit' => Pages\EditLecturer::route('/{record}/edit'),
        ];
    }
}
