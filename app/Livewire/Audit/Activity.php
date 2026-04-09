<?php

namespace App\Livewire\Audit;

use App\Models\LogActivity;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class Activity extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $userId = '';
    public $action = '';
    public $model = '';
    public $dateFrom = '';
    public $dateTo = '';

    public $selectedLogId;
    public $showDetailsModal = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'userId' => ['except' => ''],
        'action' => ['except' => ''],
        'model' => ['except' => ''],
        'dateFrom' => ['except' => ''],
        'dateTo' => ['except' => ''],
        'page' => ['except' => 1],
    ];

    public function updated($name): void
    {
        if (in_array($name, ['search', 'userId', 'action', 'model', 'dateFrom', 'dateTo'], true)) {
            $this->resetPage();
        }
    }

    public function showDetails($id): void
    {
        $this->selectedLogId = $id;
        $this->showDetailsModal = true;
    }

    public function closeDetails(): void
    {
        $this->showDetailsModal = false;
        $this->selectedLogId = null;
    }

    public function render()
    {
        view()->share('title', 'Audit activité');
        view()->share('breadcrumb', 'Audit / Activity');

        $actions = LogActivity::query()
            ->select('action')
            ->whereNotNull('action')
            ->distinct()
            ->orderBy('action')
            ->pluck('action');

        $models = LogActivity::query()
            ->select('model')
            ->whereNotNull('model')
            ->distinct()
            ->orderBy('model')
            ->pluck('model');

        $users = DB::table('users')
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        $query = LogActivity::query()
            ->with('user')
            ->when($this->search, function ($q) {
                $term = '%' . $this->search . '%';
                $q->where(function ($qq) use ($term) {
                    $qq->where('action', 'like', $term)
                        ->orWhere('model', 'like', $term)
                        ->orWhere('ip', 'like', $term)
                        ->orWhere('system', 'like', $term)
                        ->orWhere('browser', 'like', $term)
                        ->orWhere('machine', 'like', $term)
                        ->orWhereHas('user', function ($u) use ($term) {
                            $u->where('name', 'like', $term)->orWhere('email', 'like', $term);
                        });
                });
            })
            ->when($this->userId, fn($q) => $q->where('user_id', $this->userId))
            ->when($this->action, fn($q) => $q->where('action', $this->action))
            ->when($this->model, fn($q) => $q->where('model', $this->model))
            ->when($this->dateFrom, fn($q) => $q->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo, fn($q) => $q->whereDate('created_at', '<=', $this->dateTo))
            ->latest();

        $logs = $query->paginate(15);

        $selectedLog = null;
        if ($this->showDetailsModal && $this->selectedLogId) {
            $selectedLog = LogActivity::with('user')->find($this->selectedLogId);
        }

        return view('livewire.audit.activity', [
            'logs' => $logs,
            'actions' => $actions,
            'models' => $models,
            'users' => $users,
            'selectedLog' => $selectedLog,
        ]);
    }
}
