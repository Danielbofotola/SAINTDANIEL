@if (session()->has('impersonating_admin_id'))
    <div role="status" style="display:flex;align-items:center;justify-content:space-between;gap:14px;flex-wrap:wrap;padding:12px 16px;color:#543f14;background:#fff4cf;border:1px solid #edd98d;border-radius:8px;font-size:13px">
        <span><strong>Session de consultation administrateur.</strong> Vous êtes connecté comme {{ auth()->user()->username }}.</span>
        <form method="POST" action="{{ route('admin.impersonation.stop') }}">
            @csrf
            <button type="submit" style="padding:8px 12px;color:#fff;background:#193c39;border:0;border-radius:5px;font:inherit;font-weight:700;cursor:pointer">Revenir à mon compte administrateur</button>
        </form>
    </div>
@endif
