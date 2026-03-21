<?php

namespace App\Http\Controllers;

use App\Models\Signalement;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminStatController extends Controller
{
    public function publiques()
    {
        $total   = Signalement::count();
        $traites = Signalement::whereIn('statut', ['traite', 'classe'])->count();
        $taux    = $total > 0 ? round($traites / $total * 100, 1) : 0;

        $parCategorie = Signalement::select('categorie', DB::raw('count(*) as total'))
            ->groupBy('categorie')->get();

        $parProvince = Signalement::select('province', DB::raw('count(*) as total'))
            ->groupBy('province')->get();

        $evolution = [];
        for ($i = 5; $i >= 0; $i--) {
            $m = Carbon::now()->subMonths($i);
            $evolution[] = [
                'mois'  => $m->format('M Y'),
                'total' => Signalement::whereYear('created_at', $m->year)
                    ->whereMonth('created_at', $m->month)->count()
            ];
        }

        $db = config('database.default');
        if ($db === 'pgsql') {
            $rawAvg = 'AVG(EXTRACT(EPOCH FROM (updated_at - created_at)) / 86400)';
        } elseif ($db === 'mysql') {
            $rawAvg = 'AVG(TIMESTAMPDIFF(SECOND, created_at, updated_at) / 86400)';
        } else {
            $rawAvg = 'AVG(JULIANDAY(updated_at) - JULIANDAY(created_at))';
        }
        $avgDelay = Signalement::whereIn('statut', ['traite', 'classe'])
            ->selectRaw("$rawAvg as avg")
            ->value('avg');

        return response()->json([
            'total'            => $total,
            'taux_traitement'  => $taux,
            'delai_moyen'      => round($avgDelay ?? 0, 1),
            'par_categorie'    => $parCategorie,
            'par_province'     => $parProvince,
            'evolution_6_mois' => $evolution,
        ]);
    }

    public function admin(Request $request)
    {
        $stats = $this->publiques()->getData(true);

        $nonAttribues = Signalement::whereNull('agent_id')->count();
        $parAgent = User::where('role', 'agent')
            ->withCount('signalements')
            ->get()
            ->map(fn($u) => ['agent' => $u->name, 'total' => $u->signalements_count]);

        $stats['non_attribues'] = $nonAttribues;
        $stats['par_agent']     = $parAgent;

        return response()->json($stats);
    }
}
