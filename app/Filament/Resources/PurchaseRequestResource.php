<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PurchaseRequestResource\Pages;
use App\Filament\Resources\PurchaseRequestResource\RelationManagers;
use App\Models\PurchaseRequest;
use Carbon\Carbon;
use Filament\Tables\Actions\Action;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Support\Enums\Alignment;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use NunoMaduro\Collision\Adapters\Phpunit\State;
use PhpParser\Node\Stmt\Label;

class PurchaseRequestResource extends Resource
{
    protected static ?string $model = PurchaseRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    protected static ?string $navigationLabel = 'Purchase Request';

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    protected static ?string $navigationGroup = 'Purchasing';

    public static function form(Form $form): Form
    {
        // $now = Carbon::now();
        // $tahun = $now->year;
        // $cek = PurchaseRequest::count();
        // if ($cek == 0) {
        //     $no = 00001;
        //     $nomor = '#PR-' . $tahun . $no;
        //     dd($nomor);
        // } else {
        // }

        // $PR_Code = '#PR - ';
        // $PRC_Date = date('Y');
        // if ($PR_Code == null) {
        //     $PRC_Number = "00001 - ";
        // } else {
        //     $PRC_Number = "00000 - ";
        // }
        // $PRCode = $PR_Code . $PRC_Date . $PRC_Number;
        // $requestData = $request->validated();
        // $requestData['PR_Code'] = $PRCode;

        return $form
            ->schema([
                TextInput::make('PR_Code')->label('Purchase Request Code')->default('#PR'),
                TextInput::make('PR_Name')->label('Purchase Request Name')->required(),
                Select::make('Project')->required()
                    ->options([
                        'Zona 4' => 'Zona 4',
                        'Zona 11' => 'Zona 11',
                    ]),
                Select::make('Department')->required()
                    ->options([
                        'Administrative' => 'Administrative',
                        'Operation' => 'Operation',
                        'Business Development' => 'Business Development',
                        'Executive' => 'Executive',
                        'Manufacture' => 'Manufacture',
                    ]),
                Select::make('PurchaseType')->required()
                    ->options([
                        'Barang' => 'Barang',
                        'Jasa' => 'Jasa',
                        'Pembiayaan' => 'Pembiayaan',
                    ])->label('Purchase Type'),
                Select::make('Category')->required()
                    ->options([
                        'Operasional Kantor' => 'Operasional Kantor',
                        'Outstanding' => 'Outstanding',
                        'Manufaktur' => 'Manufaktur',
                        'Project' => 'Project',
                    ]),
                DateTimePicker::make('DueDate')->label('Due Date')->required(),
                TextInput::make('Description'),

                /* Items Detail */
                Repeater::make('items')->label('Items Detail')
                    ->relationship()
                    ->schema([
                        TextInput::make('Item_Name')->required(),
                        TextInput::make('Item_Description')->required(),
                        TextInput::make('Quantity')->numeric()->required(),
                        TextInput::make('Price')->numeric()->prefix('Rp.')->required()
                            ->reactive()
                            ->afterStateUpdated(function (Set $set, $state, Get $get) {
                                $vHarga = $get('Quantity');
                                $set('Total', $state * $vHarga);
                            })->required(),
                        TextInput::make('Unit')->numeric()->required(),
                        TextInput::make('Tax'),
                        TextInput::make('Total')->numeric(),
                    ])->columns(7)->columnSpan(2)->addActionLabel('Tambah Item')->label('Tambahkan Item')->addActionAlignment(Alignment::Start)->reorderable(true)->reorderableWithButtons()->cloneable(),

                /* Total */
                TextInput::make('Subtotal')
                    ->placeholder(function (Set $set, Get $get) {
                        $subtotal = collect($get('items'))->pluck('Total')->sum();
                        if ($subtotal == null) {
                            $set('Subtotal', 0);
                        } else {
                            $set('Subtotal', $subtotal);
                        }
                    }),
                /* Grand Total */
                TextInput::make('GrandTotal')->label('Grand Total')
                    ->placeholder(function (Set $set, Get $get) {
                        $Grandtotal = collect($get('items'))->pluck('Total')->sum();
                        if ($Grandtotal == null) {
                            $set('GrandTotal', 0);
                        } else {
                            $set('GrandTotal', $Grandtotal);
                        }
                    }),


            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('PR_Code')->label('PR Code'),
                TextColumn::make('PR_Name')->label('PR Name'),
                TextColumn::make('Project'),
                TextColumn::make('Department'),
                TextColumn::make('PurchaseType')->label('Purchase Type'),
                TextColumn::make('Category'),
                TextColumn::make('DueDate')->label('Due Date')->dateTime('D. d-M-y'),
                TextColumn::make('Description'),
            ])->searchable()
            ->emptyStateHeading('Belum ada Data Purchasing!')
            ->emptyStateDescription('Silahkan tambahkan Purchase Request')
            ->emptyStateIcon('heroicon-o-shopping-cart')
            ->emptyStateActions([
                Action::make('create')
                    ->label('Tambahkan Purchase Request')
                    ->url(route('filament.admin.resources.purchase-requests.create'))
                    ->icon('heroicon-m-plus')
                    ->button(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListPurchaseRequests::route('/'),
            'create' => Pages\CreatePurchaseRequest::route('/create'),
            'edit' => Pages\EditPurchaseRequest::route('/{record}/edit'),
        ];
    }
}
