document.addEventListener('DOMContentLoaded', () =>  {
    const sidebar = document.querySelector('.app-sidebar');
    const backdrop = document.querySelector('.sidebar-backdrop');
    document.querySelectorAll('[data-sidebar-toggle]').forEach(btn => btn.addEventListener('click', () =>  {
        sidebar?.classList.toggle('show'); backdrop?.classList.toggle('show');
    }
    ));
    backdrop?.addEventListener('click', () =>  {
        sidebar?.classList.remove('show'); backdrop?.classList.remove('show');
    }
    );
    document.querySelectorAll('[data-password-toggle]').forEach(btn => btn.addEventListener('click', () =>  {
        const input = document.getElementById(btn.dataset.passwordToggle); if (!input) return;
        input.type = input.type === 'password' ? 'text' : 'password';
        btn.innerHTML = input.type === 'password' ? '<i class="bi bi-eye"></i>' : '<i class="bi bi-eye-slash"></i>';
    }
    ));
    document.querySelectorAll('[data-confirm]').forEach(form => form.addEventListener('submit', e =>  {
        if (!confirm(form.dataset.confirm)) e.preventDefault();
    }
    ));
    const timer = document.querySelector('[data-quiz-timer]');
    if (timer)  {
        let remaining = parseInt(timer.dataset.quizTimer || '600', 10);
        const tick = () =>  {
            const m = Math.floor(remaining/60), s = remaining%60;
            timer.textContent = `${String(m).padStart(2,'0')}:${String(s).padStart(2,'0')}`;
            if (remaining <= 0)  {
                document.getElementById('quizForm')?.submit(); return;
            }
            remaining--;
        }
        ;
        tick(); setInterval(tick, 1000);
    }
    document.querySelectorAll('[data-auto-submit]').forEach(el => el.addEventListener('change', () => el.closest('form')?.submit()));
}
);
