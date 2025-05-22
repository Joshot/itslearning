<?php

namespace App\Filament\Pages;

use App\Models\Course;
use App\Models\CourseAssignment;
use App\Models\Lecturer;
use App\Models\Student;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Table;

class ManageCourseAssignments extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static string $view = 'filament.pages.manage-course-assignments';
    protected static ?string $navigationLabel = 'Kelola Assignment';

    public function table(Table $table): Table
    {
        return $table
            ->query(CourseAssignment::query())
            ->columns([
                Tables\Columns\TextColumn::make('course.course_name')
                    ->label('Course')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('User')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('role')
                    ->badge()
                    ->color(fn (string $state): string => $state === 'student' ? 'success' : 'warning'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('course_code')
                    ->relationship('course', 'course_name')
                    ->label('Course'),
                Tables\Filters\SelectFilter::make('role')
                    ->options([
                        'student' => 'Student',
                        'lecturer' => 'Lecturer',
                    ]),
            ])
            ->actions([
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->headerActions([
                Action::make('assign')
                    ->label('Assign User')
                    ->form([
                        Forms\Components\Select::make('course_code')
                            ->options(Course::pluck('course_name', 'course_code'))
                            ->searchable()
                            ->required(),
                        Forms\Components\Select::make('user_id')
                            ->options(function () {
                                $students = Student::pluck('name', 'id')->mapWithKeys(fn ($name, $id) => [$id => $name . ' (Student)']);
                                $lecturers = Lecturer::pluck('name', 'id')->mapWithKeys(fn ($name, $id) => [$id => $name . ' (Lecturer)']);
                                return $students->merge($lecturers);
                            })
                            ->searchable()
                            ->required(),
                        Forms\Components\Select::make('role')
                            ->options([
                                'student' => 'Student',
                                'lecturer' => 'Lecturer',
                            ])
                            ->required(),
                    ])
                    ->action(function (array $data) {
                        CourseAssignment::create($data);
                    }),
            ]);
    }
}
