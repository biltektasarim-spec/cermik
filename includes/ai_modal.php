<!-- AI Modal -->
<div id="ai-modal" class="modal">
    <div class="modal-content animate-in">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h2 style="margin-bottom: 0;">Çermik Rehberi (AI)</h2>
            <button onclick="app.toggleAI()" style="background: transparent; border: none; color: white; font-size: 1.5rem; cursor: pointer;">&times;</button>
        </div>
        <div id="ai-chat" style="height: 300px; overflow-y: auto; margin-bottom: 20px; border-bottom: 1px solid var(--glass-bg); padding-bottom: 10px; display: flex; flex-direction: column; gap: 10px;">
            <div class="ai-msg">Merhaba! Ben Çermik Rehberinim. Sana nasıl yardımcı olabilirim?</div>
        </div>
        <div style="background: rgba(255,255,255,0.05); border-radius: 15px; padding: 5px; display: flex; gap: 5px; border: 1px solid var(--glass-bg);">
            <input type="text" id="ai-input" placeholder="Soru yazın..." style="flex: 1; background: transparent; border: none; color: white; padding: 10px; outline: none; font-family: inherit;" onkeypress="if(event.key === 'Enter') app.askAI()">
            <button class="btn btn-primary" onclick="app.askAI()" style="width: auto; padding: 10px 20px;">
                <i class="fa-solid fa-paper-plane"></i>
            </button>
        </div>
        <button class="btn" style="background: transparent; color: var(--text-secondary); margin-top: 5px; padding: 5px;" onclick="app.toggleAI()">Kapat</button>
    </div>
</div>

<style>
.ai-msg {
    background: var(--glass-bg);
    padding: 10px 15px;
    border-radius: 15px 15px 15px 0;
    align-self: flex-start;
    max-width: 85%;
    font-size: 0.9rem;
}
.user-msg {
    background: var(--primary);
    color: white;
    padding: 10px 15px;
    border-radius: 15px 15px 0 15px;
    align-self: flex-end;
    max-width: 85%;
    font-size: 0.9rem;
}
</style>
