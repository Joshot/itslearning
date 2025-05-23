<?php

namespace App\Filament\Pages;

use App\Models\Course;
use App\Models\CourseAssignment;
use App\Models\Lecturer;
use App\Models\Student;
use Filament\Pages\Page;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Forms;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteBulkAction;

class ManageCourseAssignments extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    protected static ?string $navigationLabel = 'Kelola Assessment';
    protected static string $view = 'filament.pages.manage-course-assignments';

    public function table(Table $table): Table
    {
        return $table
            ->query(Course::query())
            ->columns([
                TextColumn::make('course_name')
                    ->label('Course')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('course_code')
                    ->label('Course Code')
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('course_code')
                    ->options(Course::pluck('course_name', 'course_code')->toArray())
                    ->label('Course'),
            ])
            ->actions([
                Action::make('view_assignments')
                    ->label('View Assignments')
                    ->url(fn (Course $record) => route('filament.admin.pages.course-assignments', ['course_code' => $record->course_code])),
                Action::make('edit')
                    ->form([
                        Forms\Components\Select::make('lecturer_ids')
                            ->label('Lecturers')
                            ->options(Lecturer::pluck('name', 'id')->toArray())
                            ->searchable()
                            ->multiple()
                            ->required(),
                        Forms\Components\Select::make('student_ids')
                            ->label('Students')
                            ->options(Student::pluck('name', 'id')->toArray())
                            ->searchable()
                            ->multiple()
                            ->required(),
                    ])
                    ->action(function (Course $record, array $data) {
                        // Delete existing assignments for this course
                        CourseAssignment::where('course_code', $record->course_code)->delete();

                        // Create new assignments for lecturers
                        foreach ($data['lecturer_ids'] as $userId) {
                            CourseAssignment::create([
                                'course_code' => $record->course_code,
                                'user_id' => $userId,
                            ]);
                        }

                        // Create new assignments for students
                        foreach ($data['student_ids'] as $userId) {
                            CourseAssignment::create([
                                'course_code' => $record->course_code,
                                'user_id' => $userId,
                            ]);
                        }
                    }),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->form([
                        Forms\Components\Select::make('course_code')
                            ->options(Course::pluck('course_name', 'course_code')->toArray())
                            ->required(),
                        Forms\Components\Select::make('lecturer_ids')
                            ->label('Lecturers')
                            ->options(Lecturer::pluck('name', 'id')->toArray())
                            ->searchable()
                            ->multiple()
                            ->required(),
                        Forms\Components\Select::make('student_ids')
                            ->label('Students')
                            ->options(Student::pluck('name', 'id')->toArray())
                            ->searchable()
                            ->multiple()
                            ->required(),
                    ])
                    ->action(function (array $data) {
                        // Create assignments for lecturers
                        foreach ($data['lecturer_ids'] as $userId) {
                            CourseAssignment::create([
                                'course_code' => $data['course_code'],
                                'user_id' => $userId,
                            ]);
                        }

                        // Create assignments for students
                        foreach ($data['student_ids'] as $userId) {
                            CourseAssignment::create([
                                'course_code' => $data['course_code'],
                                'user_id' => $userId,
                            ]);
                        }
                    }),
            ]);
    }
}
