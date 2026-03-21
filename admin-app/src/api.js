import axios from 'axios'

let token = null
export const setToken = (t) => { token = t }
export const getToken = () => token
export const clearToken = () => { token = null }

const api = axios.create({ baseURL: 'http://localhost:8000/api' })

api.interceptors.request.use(config => {
  if (token) config.headers.Authorization = `Bearer ${token}`
  return config
})

api.interceptors.response.use(
  r => r,
  err => {
    if (err.response?.status === 401) {
      clearToken()
      window.location.href = '/login'
    }
    return Promise.reject(err)
  }
)

export default api
