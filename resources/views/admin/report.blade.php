<x-app-layout>
    <x-slot:title>Détail du signalement #{{ $report->id }} - Insociable</x-slot:title>

    <div class="reading-container" style="padding-top: 32px; padding-bottom: 80px;">
        <nav style="margin-bottom: 24px;">
            <a href="{{ route('admin.index') }}" class="no-underline" style="font-size: 14px; color: var(--muted);">← Retour au tableau d'administration</a>
        </nav>

        <h1 class="section-title">Signalement #{{ $report->id }}</h1>

        <div class="surface-card" style="margin-bottom: 28px;">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px; font-size: 14px;">
                <div><strong>Signalé par :</strong> @&lrm;{{ $report->reporter->profile->handle }}</div>
                <div><strong>Date :</strong> {{ $report->created_at->format('d/m/Y à H:i:s') }}</div>
                <div><strong>Type de cible :</strong> {{ strtoupper($report->target_type) }} #{{ $report->target_id }}</div>
                <div><strong>Motif :</strong> {{ $report->reason }}</div>
            </div>

            @if($report->details)
                <div style="background: var(--paper); padding: 12px; border-radius: var(--radius); font-size: 14px; margin-bottom: 16px;">
                    <strong>Précision du signaleur :</strong><br>
                    {{ $report->details }}
                </div>
            @endif

            <!-- Preuve snapshot immuable -->
            <div style="border-top: 1px solid var(--border); padding-top: 16px;">
                <h2 style="font-size: 16px; margin-bottom: 8px; color: var(--muted); text-transform: uppercase;">
                    Preuve archivée (Copie immuable au moment du signalement)
                </h2>
                <div style="background: #2a2927; color: #f2efe8; padding: 16px; border-radius: var(--radius); font-family: monospace; font-size: 13px; overflow-x: auto;">
                    <pre style="margin: 0;">{{ json_encode($report->evidence?->snapshot, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                </div>
            </div>
        </div>

        <!-- Décision de modération sur ce signalement -->
        <section class="surface-card" style="margin-bottom: 28px;">
            <h2 style="font-size: 18px; margin-bottom: 16px;">Décision sur le signalement</h2>
            <form method="POST" action="{{ route('admin.reports.resolve', $report->id) }}">
                @csrf
                <div class="form-group">
                    <label class="form-label">Note / Motif de la résolution (obligatoire) :</label>
                    <textarea name="resolution_note" class="form-textarea" rows="3" required placeholder="Exemple : Contenu contraire aux règles ou Signalement infondé...">{{ old('resolution_note', $report->resolution_note) }}</textarea>
                </div>
                <div style="display: flex; gap: 12px; margin-top: 16px;">
                    <button type="submit" name="status" value="resolved" class="btn btn-primary btn-sm">Marquer comme Résolu</button>
                    <button type="submit" name="status" value="dismissed" class="btn btn-secondary btn-sm">Classer sans suite</button>
                </div>
            </form>
        </section>

        <!-- Actions directes si la cible est un Post -->
        @if($report->target_type === 'post' && $targetInstance)
            <section class="surface-card" style="margin-bottom: 28px; border-left: 4px solid var(--danger);">
                <h2 style="font-size: 18px; margin-bottom: 16px; color: var(--danger);">Actions sur la publication #{{ $targetInstance->id }}</h2>
                <div style="margin-bottom: 16px; font-size: 14px;">
                    <strong>Statut actuel :</strong> {{ $targetInstance->status }}<br>
                    <strong>Auteur :</strong> @&lrm;{{ $targetInstance->user->profile->handle }}
                </div>
                <form method="POST" action="{{ route('admin.posts.moderate', $targetInstance->id) }}" style="margin-bottom: 16px;">
                    @csrf
                    <div class="form-group">
                        <input type="text" name="reason" class="form-input" placeholder="Motif obligatoire pour la publication..." required style="min-height: 38px; font-size: 14px;">
                    </div>
                    @if($targetInstance->status === 'published')
                        <button type="submit" name="action" value="remove" class="btn btn-danger btn-sm">Retirer immédiatement du fil</button>
                    @else
                        <button type="submit" name="action" value="restore" class="btn btn-secondary btn-sm">Restaurer la publication</button>
                    @endif
                </form>

                <!-- Action sur l'auteur -->
                <form method="POST" action="{{ route('admin.users.moderate', $targetInstance->user_id) }}">
                    @csrf
                    <div class="form-group">
                        <input type="text" name="reason" class="form-input" placeholder="Motif obligatoire pour la sanction de l'auteur..." required style="min-height: 38px; font-size: 14px;">
                    </div>
                    <div style="display: flex; gap: 10px;">
                        <button type="submit" name="action" value="suspend_24h" class="btn btn-danger btn-sm">Suspendre l'auteur 24h</button>
                        <button type="submit" name="action" value="ban" class="btn btn-danger btn-sm">Bannir définitivement l'auteur</button>
                    </div>
                </form>
            </section>
        @endif
    </div>
</x-app-layout>
