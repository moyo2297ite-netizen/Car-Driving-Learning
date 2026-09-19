import { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import Layout from '../components/Layout';
import Icon from '../components/Icon';
import { useAuth } from '../hooks/useAuth';
import ConversationView from '../components/ConversationView';
import { fetchConversation, sendMessage } from '../api/messages';

// الطالب بيراسل المشرف/الأدمن بس — ما في تواصل مباشر مع المدرب إطلاقاً.
// بما إنه v1 مدرسة بمشرف واحد، بنفترض هويته معروفة (id=2 بالبيانات
// التجريبية الحالية). TODO لاحقًا: شاشة اختيار لو صار أكتر من مشرف.
const supervisorId = 2;

function StudentMessages() {
  const { user } = useAuth();
  const [messages, setMessages] = useState(null);
  const [error, setError] = useState('');

  useEffect(() => {
    fetchConversation(supervisorId)
      .then(setMessages)
      .catch((err) => setError(err.message));
  }, []);

  const handleSend = async (body) => {
    try {
      const message = await sendMessage(supervisorId, body);
      setMessages((prev) => [...prev, message]);
    } catch (err) {
      setError(err.message);
    }
  };

  return (
    <Layout roleLabel="طالب">
      <h1>
        <Icon name="bell" size={24} />
        راسل المشرف
      </h1>
      <p className="page-subtitle">تواصل مباشر مع المشرف فقط — لا يوجد تواصل مع المدرب داخل التطبيق.</p>

      {error && <p className="card">{error}</p>}
      {!messages && !error && <p className="page-subtitle">جاري التحميل...</p>}

      {messages && <ConversationView messages={messages} currentUserId={user.id} onSend={handleSend} />}

      <Link className="back-link" to="/student">
        <Icon name="chevron" size={16} />
        العودة إلى اللوحة الرئيسية
      </Link>
    </Layout>
  );
}

export default StudentMessages;
