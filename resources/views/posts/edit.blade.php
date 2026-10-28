<x-app-layout>
    <x-slot:title>Modifier la publication - Insociable</x-slot:title>

    <div class="reading-container" style="max-width: 600px; padding-top: 32px; padding-bottom: 60px;">
        <h1 class="section-title">Modifier la publication</h1>
        <p style="color: var(--muted); margin-bottom: 24px; font-size: 14px;">
            La date de parution d'origine reste inchangée : la publication ne remontera pas dans le fil.
        </p>

        <form method="POST" action="{{ route('posts.update', $post->id) }}" class="surface-card">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="body" class="form-label">Texte de la publication</label>
                <textarea id="body"
                          name="body"
                          class="form-textarea"
                          rows="6"
                          maxlength="{{ config('insociable.post_max_length', 2000) }}"
                          required>{{ old('body', $post->body) }}</textarea>
                @error('body')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group" style="margin-top: 18px;">
                <label class="form-check">
                    <input type="checkbox" name="comments_allowed" value="1" {{ old('comments_allowed', $post->comments_allowed) ? 'checked' : '' }}>
                    <span style="font-size: 14px;">Autoriser les commentaires sur cette publication</span>
                </label>
            </div>

            <div style="display: flex; gap: 12px; margin-top: 24px;">
                <button type="submit" class="btn btn-primary">
                    Enregistrer les modifications
                </button>
                <a href="{{ route('posts.show', $post->id) }}" class="btn btn-secondary">
                    Annuler
                </a>
            </div>
        </form>
    </div>
</x-app-layout>
