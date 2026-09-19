import { useEffect, useState } from 'react';
import Layout from '../components/Layout';
import Icon from '../components/Icon';
import { useAuth } from '../hooks/useAuth';
import ConversationView from '../components/ConversationView';
import { fetchThreads, fetchConversation, sendMessage } from '../api/messages';

function SupervisorMessages() {
  const { user } = useAuth();
  const [threads, setThreads] = useState(null);
  const [error, setError] = useState('');
  const [activeThreadId, setActiveThreadId] = useState(null);
  const [messages, setMessages] = useState(null);
  // نتذكر لأي thread تنتمي آخر رسائل توصّلنا — هيك منعرف إذا "messages"
  // الحالية بتخص المحادثة المفتوحة هلق، أو لسا بيانات محادثة قديمة
  const [messagesThreadId, setMessagesThreadId] = useState(null);

  useEffect(() => {
    fetchThreads()
      .then((data) => {
        setThreads(data);
        if (data.length > 0) setActiveThreadId(data[0].id);
      })
      .catch((err) => setError(err.message));
  }, []);

  useEffect(() => {
    if (!activeThreadId) return;
    fetchConversation(activeThreadId)
      .then((data) => {
        setMessages(data);
        setMessagesThreadId(activeThreadId);
      })
      .catch((err) => setError(err.message));
  }, [activeThreadId]);

  const handleSend = async (body) => {
    try {
      const message = await sendMessage(activeThreadId, body);
      setMessages((prev) => [...prev, message]);
    } catch (err) {
      setError(err.message);
    }
  };

  return (
    <Layout roleLabel="مشرف">
      <h1>
        <Icon name="bell" size={24} />
        رسائل الطلاب
      </h1>
      <p className="page-subtitle">كل محادثاتك مع الطلاب بمكان واحد.</p>

      {error && <p className="card">{error}</p>}
      {!threads && !error && <p className="page-subtitle">جاري التحميل...</p>}
      {threads && threads.length === 0 && <p className="page-subtitle">ما في محادثات لسا.</p>}

      {threads && threads.length > 0 && (
        <div className="card">
          <h2>
            <Icon name="users" size={17} />
            المحادثات ({threads.length})
          </h2>
          <div className="thread-list">
            {threads.map((thread) => (
              <button
                key={thread.id}
                className={activeThreadId === thread.id ? 'active' : ''}
                onClick={() => setActiveThreadId(thread.id)}
              >
                {thread.name}
              </button>
            ))}
          </div>
        </div>
      )}

      {activeThreadId && messages && messagesThreadId === activeThreadId && (
        <ConversationView messages={messages} currentUserId={user.id} onSend={handleSend} />
      )}
    </Layout>
  );
}

export default SupervisorMessages;
