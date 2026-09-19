import { useState } from 'react';
import Icon from './Icon';

function formatTime(iso) {
  return new Date(iso).toLocaleString('ar-SY', { dateStyle: 'short', timeStyle: 'short' });
}

// مكوّن محادثة عام — نفس الشكل بيستخدمه StudentMessages وSupervisorMessages،
// كل واحد بس بيمررله messages مختلفة وdالة onSend مختلفة. هيك بنكتب منطق
// المحادثة مرة وحدة بس بدل ما نكرره.
function ConversationView({ messages, currentUserId, onSend }) {
  const [draft, setDraft] = useState('');

  const handleSubmit = (e) => {
    e.preventDefault();
    if (!draft.trim()) return;
    onSend(draft.trim());
    setDraft('');
  };

  return (
    <div className="card">
      <h2>
        <Icon name="bell" size={17} />
        المحادثة
      </h2>

      <div className="chat-thread">
        {messages.length === 0 && <p>ما في رسائل لسا.</p>}
        {messages.map((message) => {
          const isOwn = message.sender.id === currentUserId;
          return (
            <div key={message.id} className={`chat-bubble ${isOwn ? 'own' : 'other'}`}>
              {message.body}
              <span className="chat-meta">{formatTime(message.created_at)}</span>
            </div>
          );
        })}
      </div>

      <form className="chat-input-row" onSubmit={handleSubmit}>
        <input
          value={draft}
          onChange={(e) => setDraft(e.target.value)}
          placeholder="اكتب رسالتك..."
        />
        <button type="submit">إرسال</button>
      </form>
    </div>
  );
}

export default ConversationView;
