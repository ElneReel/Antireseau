<x-app-layout>
    <x-slot:title>Espace Modération & Administration - Insociable</x-slot:title>

    <div class="site-container" style="padding-top: 32px; padding-bottom: 80px;">
        <div style="display: flex; justify-content: space-between; align-items: baseline; border-bottom: 2px solid var(--accent); padding-bottom: 12px; margin-bottom: 28px;">
            <h1 class="section-title" style="margin-bottom: 0;">Administration & Modération</h1>
            <span style="font-size: 14px; background: var(--accent-soft); color: var(--accent); padding: 4px 10px; border-radius: 4px; font-weight: 600;">
                Rôle Administrateur
            </span>
        </div>

        <!-- Métriques clés -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 36px;">
            <div class="surface-card">
                <div style="font-size: 13px; color: var(--muted); text-transform: uppercase;">Signalements ouverts</div>
                <div style="font-size: 32px; font-weight: 600; color: var(--danger);">{{ $stats['open_reports'] }}</div>
            </div>
            <div class="surface-card">
                <div style="font-size: 13px; color: var(--muted); text-transform: uppercase;">Contestations en attente</div>
                <div style="font-size: 32px; font-weight: 600; color: var(--accent);">{{ $stats['pending_appeals'] }}</div>
            </div>
            <div class="surface-card">
                <div style="font-size: 13px; color: var(--muted); text-transform: uppercase;">Membres suspendus (24h)</div>
                <div style="font-size: 32px; font-weight: 600;">{{ $stats['suspended_users'] }}</div>
            </div>
            <div class="surface-card">
                <div style="font-size: 13px; color: var(--muted); text-transform: uppercase;">Comptes bannis</div>
                <div style="font-size: 32px; font-weight: 600;">{{ $stats['banned_users'] }}</div>
            </div>
        </div>

        <!-- Contestations en attente -->
        @if($pendingAppeals->isNotEmpty())
            <section class="surface-card" style="margin-bottom: 36px; border-left: 4px solid var(--accent);">
                <h2 style="font-size: 20px; margin-bottom: 16px;">Contestations de suspension ({{ $pendingAppeals->count() }})</h2>
                @foreach($pendingAppeals as $appeal)
                    <div style="border-bottom: 1px solid var(--border); padding-bottom: 16px; margin-bottom: 16px;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 6px;">
                            <strong>@&lrm;{{ $appeal->user->profile->handle }}</strong>
                            <span style="font-size: 13px; color: var(--muted);">{{ $appeal->created_at->format('d/m/Y à H:i') }}</span>
                        </div>
                        <div style="font-style: italic; background: var(--paper); padding: 10px; border-radius: var(--radius); margin-bottom: 12px; font-size: 15px;">
                            « {{ $appeal->message }} »
                        </div>
                        <form method="POST" action="{{ route('admin.appeals.resolve', $appeal->id) }}" style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                            @csrf
                            <input type="text" name="reason" class="form-input" placeholder="Motif obligatoire de la décision..." required style="min-height: 36px; max-width: 320px; font-size: 13px;">
                            <button type="submit" name="decision" value="accept" class="btn btn-primary btn-sm">Accepter & Lever suspension</button>
                            <button type="submit" name="decision" value="reject" class="btn btn-secondary btn-sm">Rejeter</button>
                        </form>
                    </div>
                @endforeach
            </section>
        @endif

        <!-- Tableau des signalements -->
        <section class="surface-card" style="margin-bottom: 36px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 12px;">
                <h2 style="font-size: 20px; margin: 0;">Signalements reçus</h2>
                <div style="display: flex; gap: 8px;">
                    <a href="{{ route('admin.index', ['status' => 'open']) }}" class="btn btn-sm {{ $currentStatus === 'open' ? 'btn-primary' : 'btn-secondary' }}">Ouverts</a>
                    <a href="{{ route('admin.index', ['status' => 'resolved']) }}" class="btn btn-sm {{ $currentStatus === 'resolved' ? 'btn-primary' : 'btn-secondary' }}">Traités</a>
                    <a href="{{ route('admin.index', ['status' => 'all']) }}" class="btn btn-sm {{ $currentStatus === 'all' ? 'btn-primary' : 'btn-secondary' }}">Tous</a>
                </div>
            </div>

            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 14px;">
                    <thead>
                        <tr style="border-bottom: 2px solid var(--border); color: var(--muted);">
                            <th style="padding: 10px 8px;">Date</th>
                            <th style="padding: 10px 8px;">Signaleur</th>
                            <th style="padding: 10px 8px;">Type</th>
                            <th style="padding: 10px 8px;">Motif</th>
                            <th style="padding: 10px 8px;">Statut</th>
                            <th style="padding: 10px 8px; text-align: right;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reports as $rep)
                            <tr style="border-bottom: 1px solid var(--border);">
                                <td style="padding: 12px 8px; white-space: nowrap;">{{ $rep->created_at->format('d/m/Y H:i') }}</td>
                                <td style="padding: 12px 8px;">@&lrm;{{ $rep->reporter->profile->handle }}</td>
                                <td style="padding: 12px 8px;"><span style="text-transform: uppercase; font-size: 12px; font-weight: 600;">{{ $rep->target_type }}</span> #{{ $rep->target_id }}</td>
                                <td style="padding: 12px 8px;">{{ $rep->reason }}</td>
                                <td style="padding: 12px 8px;">
                                    <span style="font-size: 12px; padding: 2px 8px; border-radius: 4px; font-weight: 500; {{ $rep->status === 'open' ? 'background: #fdf2f2; color: var(--danger);' : 'background: #edf7ed; color: #1e4620;' }}">
                                        {{ $rep->status === 'open' ? 'Ouvert' : ($rep->status === 'resolved' ? 'Traité' : 'Rejeté') }}
                                    </span>
                                </td>
                                <td style="padding: 12px 8px; text-align: right;">
                                    <a href="{{ route('admin.reports.show', $rep->id) }}" class="btn btn-secondary btn-sm" style="font-size: 13px; padding: 4px 10px;">
                                        Examiner
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" style="padding: 24px; text-align: center; color: var(--muted);">
                                    Aucun signalement dans cette catégorie.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div style="margin-top: 20px;">
                {{ $reports->links() }}
            </div>
        </section>

        <!-- Journal récent des décisions -->
        <section class="surface-card">
            <h2 style="font-size: 20px; margin-bottom: 16px;">Journal des décisions de modération</h2>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 13px;">
                    <thead>
                        <tr style="border-bottom: 1px solid var(--border); color: var(--muted);">
                            <th style="padding: 8px;">Date</th>
                            <th style="padding: 8px;">Modérateur</th>
                            <th style="padding: 8px;">Action</th>
                            <th style="padding: 8px;">Motif consigné</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentActions as $action)
                            <tr style="border-bottom: 1px solid var(--border);">
                                <td style="padding: 8px; white-space: nowrap;">{{ $action->created_at->format('d/m/Y H:i') }}</td>
                                <td style="padding: 8px;">@&lrm;{{ $action->moderator->profile->handle }}</td>
                                <td style="padding: 8px; font-weight: 600;">{{ $action->action }}</td>
                                <td style="padding: 8px;">{{ $action->reason }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" style="padding: 16px; text-align: center; color: var(--muted);">
                                    Aucune action consignée pour l'instant.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</x-app-layout>
