import { defineStore } from 'pinia'
import api from '../services/api'

export const useAuthStore = defineStore('auth', {
    state: () => ({
        token: localStorage.getItem('token') || '',
        user: JSON.parse(localStorage.getItem('user') || 'null')
    }),

    getters: {
        isAuthenticated: (state) => !!state.token
    },

    actions: {

        async login(email: string, password: string) {

            const response = await api.post('/login', {
                email,
                password
            })

            this.token = response.data.token
            this.user = response.data.user

            localStorage.setItem('token', this.token)
            localStorage.setItem('user', JSON.stringify(this.user))

            api.defaults.headers.common['Authorization'] = `Bearer ${this.token}`

            return response.data
        },

        logout() {

            this.token = ''
            this.user = null

            localStorage.removeItem('token')
            localStorage.removeItem('user')

            delete api.defaults.headers.common['Authorization']
        }
    }
})