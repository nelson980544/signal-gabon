import { Routes, Route } from 'react-router-dom'
import Accueil from './pages/Accueil'
import Signaler from './pages/Signaler'
import Confirmation from './pages/Confirmation'
import Suivi from './pages/Suivi'
import Stats from './pages/Stats'
import Guide from './pages/Guide'
import Confidentialite from './pages/Confidentialite'
import Conditions from './pages/Conditions'
import Protection from './pages/Protection'

export default function App() {
  return (
    <Routes>
      <Route path="/" element={<Accueil />} />
      <Route path="/signaler" element={<Signaler />} />
      <Route path="/confirmation" element={<Confirmation />} />
      <Route path="/suivi" element={<Suivi />} />
      <Route path="/stats" element={<Stats />} />
      <Route path="/guide" element={<Guide />} />
      <Route path="/confidentialite" element={<Confidentialite />} />
      <Route path="/conditions" element={<Conditions />} />
      <Route path="/protection" element={<Protection />} />
    </Routes>
  )
}
