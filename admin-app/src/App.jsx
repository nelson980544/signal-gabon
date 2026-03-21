import { Routes, Route, Navigate } from 'react-router-dom'
import { useAuth } from './AuthContext'
import Login from './pages/Login'
import Layout from './components/Layout'
import Dashboard from './pages/Dashboard'
import Signalements from './pages/Signalements'
import SignalementDetail from './pages/SignalementDetail'
import StatsAdmin from './pages/StatsAdmin'
import Agents from './pages/Agents'

function ProtectedRoute({ children }) {
  const { user } = useAuth()
  return user ? children : <Navigate to="/login" replace />
}

export default function App() {
  return (
    <Routes>
      <Route path="/login" element={<Login />} />
      <Route path="/" element={<ProtectedRoute><Layout /></ProtectedRoute>}>
        <Route index element={<Navigate to="/dashboard" replace />} />
        <Route path="dashboard" element={<Dashboard />} />
        <Route path="signalements" element={<Signalements />} />
        <Route path="signalements/:id" element={<SignalementDetail />} />
        <Route path="stats" element={<StatsAdmin />} />
        <Route path="agents" element={<Agents />} />
      </Route>
    </Routes>
  )
}
