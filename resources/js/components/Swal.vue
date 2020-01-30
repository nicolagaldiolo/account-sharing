<template>
    <!--<div v-if="form.successful" class="alert alert-success alert-dismissible" role="alert">
        <button v-if="dismissible" type="button" class="close" aria-label="Close" @click="dismiss">
            <span aria-hidden="true">&times;</span>
        </button>

        <slot>
            <div v-html="message"/>
        </slot>
    </div>-->
</template>

<script>
    import Swal from 'sweetalert2'

    export default {
        name: 'Swal',

        props: {
            type: {
                type: String,
                default: 'success'
            },
            titòe: {
                type: String,
                default: ''
            },
            text: {
                type: String,
                default: ''
            }
        },

        data: () => ({
            //form: new Form({
            //    username: '',
            //    password: ''
            //}),
            //showCredential: false
        }),

        created () {
            
            Swal.fire({
                type: 'success',
                title: 'Credenziali aggiornate',
                text: 'Credenziali aggiornate da',
            })
        }
    }
</script>
