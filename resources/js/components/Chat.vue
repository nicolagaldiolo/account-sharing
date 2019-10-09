<template>
  <div v-if="owner || joined">
    <h4>Chat del gruppo</h4>
    <div class="mesgs">
      <div id="msg_container" class="msg_history">

        <infinite-loading direction="top" spinner="waveDots" @infinite="infiniteHandler"></infinite-loading>

        <div v-if="chatsFormatted && chatsFormatted.length">
          <div v-for="(chatGroup, index) in chatsFormatted" :key="index">

            <div class="date">
              <small>{{chatGroup.date}}</small>
            </div>

            <div v-for="(chat, index) in chatGroup.chats" :key="index">
              <div v-if="chat.user.id !== authUser.id" class="incoming_msg">
                <div class="incoming_msg_img">
                  <img class="rounded-circle" :src="chat.user.photo_url">
                </div>
                <div class="received_msg">
                  <div class="received_withd_msg">
                    <p>
                      <strong>{{ chat.user.name }}</strong><br>
                      {{ chat.message }}
                      <span class="time_date">{{ chat.created_at | moment("h:mm a") }}</span>
                    </p>
                  </div>
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
                    <span class="time_date">{{ chat.created_at | moment("h:mm a") }}</span>
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>

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
    import InfiniteLoading from 'vue-infinite-loading'
    import Form from 'vform'

    export default {
        name: 'Chat',
        middleware: 'auth',
        components: {
            InfiniteLoading
        },

        props: {
            authUser: {
                type: Object,
                default: null
            },
            sharing: {
                type: Object,
                default: null
            },
            joined: {
                type: Boolean,
                default: false
            },
            owner: {
                type: Boolean,
                default: false
            }
        },

        data () {
            return {
                chats : {},
                scrollChatBox: false,
                current_page: 1,
                form: new Form({
                    message: ''
                })
            }
        },

        created () {
            if(this.owner || this.joined) {
                window.Echo.private(`chatSharing.${this.sharing.id}`)
                    .listen('ChatMessageSent', (e) => this.appendChatMessage(e.chat))
            }
        },

        updated: function () {
            if(this.scrollChatBox){
                var container = document.getElementById('msg_container');
                container.scrollTop = container.scrollHeight - container.clientHeight;
                this.scrollChatBox = false;
            }
        },

        computed: {
            emptyMessage: function () {
                return this.form.message === ''
            },

            chatsFormatted: function() {
                const groupArrays = Object.keys(this.chats).map((date) => {
                    return {
                        date,
                        chats: this.chats[date].sort( (a,b) => new Date(a.created_at) - new Date(b.created_at))
                    }

                }).sort( (a,b) => new Date(a.date) - new Date(b.date));

                return groupArrays;
            }

        },

        methods: {

            async postChatMessage () {
                const { data } = await this.form.post(`/api/sharings/${this.sharing.id}/chat`)
                this.appendChatMessage(data, true)
            },

            async infiniteHandler ($state) {

                    const id = this.$route.params.sharing_id
                    const currentPage = this.current_page

                    this.$store.dispatch('sharings/fetchChats', {id, currentPage}).then(response => {
                        if (response) {
                            const chatStore = this.$store.getters['sharings/chats']
                            if (this.current_page <= chatStore.last_page) {
                                this.current_page += 1

                                // devo clonare l'oggetto dello stato interno altrimenti il componente non si accorge dei cambiamenti e non scatena il redender
                                const clone = Object.assign({}, this.chats);

                                chatStore.data.reduce((obj, chat) => {
                                    return this.addSingleItem(obj, chat);
                                }, clone);

                                this.chats = clone;
                                $state.loaded()

                            } else {
                                $state.complete()
                            }
                        }
                    })
            },

            addSingleItem: function(obj, message) {
                const date = message.created_at.split(' ')[0];
                if (!obj[date]) {
                    obj[date] = [];
                }
                obj[date].push(message);
                return obj;
            },

            appendChatMessage: function (message, cleanForm = false) {
                // devo clonare l'oggetto dello stato interno altrimenti il componente non si accorge dei cambiamenti e non scatena il redender
                const clone = Object.assign({}, this.chats);

                this.chats = this.addSingleItem(clone, message);
                this.scrollChatBox = true
                if (cleanForm) {
                    this.form.message = ''
                }
            }
        }
    }
</script>

<style scoped>

  img{ max-width:100%;}

  .date{
    display:block;
    text-align:center;
    position: sticky;
    top:0;
  }

  .date small{
    background: rgba(40, 167, 69, .3);
    padding: 2px 10px;
    display: inline-block;
    border-radius: 5px;
    font-size: 60%;
  }

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
    height: 520px;
    overflow-x: hidden;
    overflow-y: visible;
  }

</style>
