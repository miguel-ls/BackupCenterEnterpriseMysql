import { defineStore } from 'pinia'

import { getStatus } from '../api/client'

export const useStatusStore = defineStore('status', {

    state: () => ({

        loading: false,

        data: {

            version: '',

            service: '',

            php: '',

            database: false,

            time: ''

        }

    }),

    actions: {

        async load() {

            this.loading = true

            try {

                this.data = await getStatus()

            }
            finally {

                this.loading = false

            }

        }

    }

})