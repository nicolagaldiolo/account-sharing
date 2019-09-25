<template>
    <div>
        <h4>Chat del gruppo</h4>
        <div class="mesgs">
            <div class="msg_history">
                <div v-if="sharing.chats.length">
                    <div v-for="chat in sharing.chats" :key="chat.id">
                        <div v-if="chat.user.id !== authUser.id" class="incoming_msg">
                            <div class="incoming_msg_img">
                                <img class="rounded-circle" :src="chat.user.photo_url">
                            </div>
                            <div class="received_msg">
                                <div class="received_withd_msg">
                                    <p>
                                        <strong>{{ chat.user.name }}</strong><br>
                                        {{ chat.message }}
                                    </p>
                                    <span class="time_date">{{ chat.created_at | moment("D MMMM YYYY, h:mm a") }}</span></div>
                            </div>
                        </div>
                        <div v-else class="outgoing_msg">
                            <div class="outgoing_msg_img">
                                <img class="rounded-circle" :src="chat.user.photo_url">
                            </div>
                            <div class="sent_msg">
                                <p>
                                    <strong>{{ chat.user.name }}</strong><br>
                                    {{ chat.message }}
                                </p>
                                <span class="time_date">{{ chat.created_at | moment("D MMMM YYYY, h:mm a") }}</span></div>
                        </div>
                    </div>
                </div>
                <h1 v-else>Nessun messaggio in chat</h1>
            </div>
            <div class="type_msg">
                <div class="input_msg_write">
                    <input type="text" class="write_msg" v-model="form.message" placeholder="Type a message">
                    <button :disabled="emptyMessage" @click="postChatMessage" class="msg_send_btn" type="button"><i class="fa fa-paper-plane-o" aria-hidden="true"></i></button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import Form from 'vform'

export default {
    name: 'Chat',
    middleware: 'auth',
    props: {
        authUser: {
            type: Object,
            default: null
        },
        sharing: {
            type: Object,
            default: null
        }
    },

    data () {
        return {
            form: new Form({
                message: ''
            })
        }
    },

    created(){
        Echo.private(`chatSharing.${this.sharing.id}`).listen('ChatMessageSent', (e) => {
            this.$store.dispatch('sharings/addChatMessage', { chat: e.chat })
        });
    },

    computed: {
        emptyMessage: function () {
            return this.form.message === ''
        }
    },

    methods: {
        async postChatMessage () {
            const { data } = await this.form.post(`/api/sharings/${this.sharing.id}/chat`)
            this.$store.dispatch('sharings/addChatMessage', { chat: data })
            this.form.message = ''
        },
    }
}
</script>

<style scoped>

    img{ max-width:100%;}

    .recent_heading h4 {
        color: #05728f;
        font-size: 21px;
        margin: auto;
    }
    .srch_bar input{ border:1px solid #cdcdcd; border-width:0 0 1px 0; width:80%; padding:2px 0 4px 6px; background:none;}
    .srch_bar .input-group-addon button {
        background: rgba(0, 0, 0, 0) none repeat scroll 0 0;
        border: medium none;
        padding: 0;
        color: #707070;
        font-size: 18px;
    }
    .srch_bar .input-group-addon { margin: 0 0 0 -27px;}

    .chat_ib h5{ font-size:15px; color:#464646; margin:0 0 8px 0;}
    .chat_ib h5 span{ font-size:13px; float:right;}
    .chat_ib p{ font-size:14px; color:#989898; margin:auto}


    .outgoing_msg_img{
        order: 1;
        padding-left: 10px;
    }

    .outgoing_msg_img,
    .incoming_msg_img {
        display: inline-block;
        width: 6%;
    }
    .received_msg {
        display: inline-block;
        padding: 0 0 0 10px;
        vertical-align: top;
        width: 92%;
    }
    .received_withd_msg p {
        background: #ebebeb none repeat scroll 0 0;
        border-radius: 3px;
        color: #646464;
        font-size: 14px;
        margin: 0;
        padding: 5px 10px 5px 12px;
        width: 100%;
    }
    .time_date {
        color: #747474;
        display: block;
        font-size: 12px;
        margin: 8px 0 0;
    }
    .received_withd_msg { width: 57%;}

    .sent_msg p {
        background: #05728f none repeat scroll 0 0;
        border-radius: 3px;
        font-size: 14px;
        margin: 0; color:#fff;
        padding: 5px 10px 5px 12px;
        width:100%;
    }
    .incoming_msg,
    .outgoing_msg{
        overflow:hidden;
        margin:26px 0 0 0;
    }

    .outgoing_msg {
        display: flex;
        justify-content: flex-end;
    }

    .sent_msg {
        float: right;
        width: 46%;
    }
    .input_msg_write input {
        background: rgba(0, 0, 0, 0) none repeat scroll 0 0;
        border: medium none;
        color: #4c4c4c;
        font-size: 15px;
        min-height: 48px;
        width: 100%;
    }

    .type_msg {border-top: 1px solid #c4c4c4;position: relative;}

    .msg_send_btn {
        background: #05728f none repeat scroll 0 0;
        border: medium none;
        border-radius: 50%;
        color: #fff;
        cursor: pointer;
        font-size: 17px;
        height: 33px;
        position: absolute;
        right: 0;
        top: 11px;
        width: 33px;
    }

    .msg_send_btn:disabled{
        background: #d9d9d9;
        cursor: default;
    }

    .msg_history {
        height: 516px;
        overflow-y: auto;
    }

</style>
