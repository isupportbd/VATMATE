<?php

namespace Modules\Report\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Report\Events\ExcelExportEvent;
use Modules\Report\Exports\Report63Export;

class Report63ExportJob implements ShouldQueue
{
  use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

  public function __construct(public $clientId, public $itemId, public $monthYear, public $fileName, public $userId) {}

  public function handle(): void
  {
    // dd($this->userId);
    $path = "temp_exports/{$this->fileName}";
    $export = new Report63Export($this->clientId, $this->itemId, $this->monthYear); // Always generate fresh data - no caching
    $excel = Excel::store($export, $path, 'public');

    $excel && broadcast(new ExcelExportEvent($this->fileName, $this->userId));
  }
}
