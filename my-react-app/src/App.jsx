import { Routes, Route } from 'react-router-dom';
import Login from './pages/Login';
import SignUp from './pages/SignUp';
import AdminDashboard from './pages/AdminDashboard';
import SupervisorDashboard from './pages/SupervisorDashboard';
import InstructorDashboard from './pages/InstructorDashboard';
import StudentDashboard from './pages/StudentDashboard';
import ProtectedRoute from './components/ProtectedRoute';
import StudentBooking from './pages/StudentBooking';
import StudentContent from './pages/StudentContent';
import StudentQuiz from './pages/StudentQuiz';
import StudentSkills from './pages/StudentSkills';
import StudentMessages from './pages/StudentMessages';
import InstructorStudents from './pages/InstructorStudents';
import InstructorStudentSkills from './pages/InstructorStudentSkills';
import InstructorAvailability from './pages/InstructorAvailability';
import SupervisorMessages from './pages/SupervisorMessages';
import AdminSettings from './pages/AdminSettings';
import AdminInstructors from './pages/AdminInstructors';
import AdminSupervisors from './pages/AdminSupervisors';
import AdminContent from './pages/AdminContent';
import AdminSkills from './pages/AdminSkills';
import './App.css';


function App() {
  return (
    <Routes>
      <Route path="/" element={<Login />} />
      <Route path="/register" element={<SignUp />} />

      <Route
        path="/admin"
        element={
          <ProtectedRoute allowedRole="admin">
            <AdminDashboard />
          </ProtectedRoute>
        }
      />

      <Route
        path="/supervisor"
        element={
          <ProtectedRoute allowedRole="supervisor">
            <SupervisorDashboard />
          </ProtectedRoute>
        }
      />

      <Route
        path="/instructor"
        element={
          <ProtectedRoute allowedRole="instructor">
            <InstructorDashboard />
          </ProtectedRoute>
        }
      />

      <Route
        path="/student"
        element={
          <ProtectedRoute allowedRole="student">
            <StudentDashboard />
          </ProtectedRoute>
        }
      />
      <Route
  path="/student/booking"
  element={
    <ProtectedRoute allowedRole="student">
      <StudentBooking />
    </ProtectedRoute>
  }
/>
<Route
  path="/student/content"
  element={
    <ProtectedRoute allowedRole="student">
      <StudentContent />
    </ProtectedRoute>
  }
/>
<Route
  path="/student/content/:contentId/quiz"
  element={
    <ProtectedRoute allowedRole="student">
      <StudentQuiz />
    </ProtectedRoute>
  }
/>
<Route
  path="/student/skills"
  element={
    <ProtectedRoute allowedRole="student">
      <StudentSkills />
    </ProtectedRoute>
  }
/>
<Route
  path="/student/messages"
  element={
    <ProtectedRoute allowedRole="student">
      <StudentMessages />
    </ProtectedRoute>
  }
/>
<Route
  path="/instructor/students"
  element={
    <ProtectedRoute allowedRole="instructor">
      <InstructorStudents />
    </ProtectedRoute>
  }
/>

<Route
  path="/instructor/students/:studentId/skills"
  element={
    <ProtectedRoute allowedRole="instructor">
      <InstructorStudentSkills />
    </ProtectedRoute>
  }
/>
<Route
  path="/instructor/availability"
  element={
    <ProtectedRoute allowedRole="instructor">
      <InstructorAvailability />
    </ProtectedRoute>
  }
/>
<Route
  path="/supervisor/messages"
  element={
    <ProtectedRoute allowedRole="supervisor">
      <SupervisorMessages />
    </ProtectedRoute>
  }
/>
<Route
  path="/admin/settings"
  element={
    <ProtectedRoute allowedRole="admin">
      <AdminSettings />
    </ProtectedRoute>
  }
/>
<Route
  path="/admin/instructors"
  element={
    <ProtectedRoute allowedRole="admin">
      <AdminInstructors />
    </ProtectedRoute>
  }
/>
<Route
  path="/admin/supervisors"
  element={
    <ProtectedRoute allowedRole="admin">
      <AdminSupervisors />
    </ProtectedRoute>
  }
/>
<Route
  path="/admin/content"
  element={
    <ProtectedRoute allowedRole={['admin', 'supervisor']}>
      <AdminContent />
    </ProtectedRoute>
  }
/>
<Route
  path="/admin/skills"
  element={
    <ProtectedRoute allowedRole="admin">
      <AdminSkills />
    </ProtectedRoute>
  }
/>
    </Routes>

  );

}

export default App;
