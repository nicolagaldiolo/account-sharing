<template>
  <div>
    <section class="jumbotron" :style="{'background-image': 'url(' + sharing.image + ')'}">
      <div class="container">
        <div class="row">
          <div class="col-sm-6">
            <div class="card">
              <div class="card-body">
                <img :src="sharing.owner.photo_url">
                <h5 class="card-title">{{sharing.owner.name}}</h5>
                <p class="card-text">With supporting text below as a natural lead-in to additional content.</p>
                <a href="#" class="btn btn-primary">Go somewhere</a>
              </div>
            </div>
          </div>
          <div class="col-sm-6">
            <div class="card">
              <div class="card-body">
                <h5 class="card-title">{{sharing.name}}</h5>
                <p class="card-text">{{sharing.description}}</p>
                <!--<a href="#" class="btn btn-primary">Go somewhere</a>-->
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
    <div class="container">
      <div v-if="owner || joined">
        <a v-if="availability" class="btn btn-primary btn-lg btn-block">Invita altra gente</a>
      </div>
      <div v-else-if="foreign">
        <a @click.prevent="joinGroup" class="btn btn-primary btn-lg btn-block">Entra nel gruppo</a>
      </div>
      <div v-else>
        <div v-if="sharing.sharing_state_machine.transitions.length">
          <a v-for="(transition, index) in sharing.sharing_state_machine.transitions" :key="index" @click.prevent="doTransition(transition.value)" class="btn btn-primary btn-lg btn-block">
            {{transition.metadata.title}}
          </a>
        </div>
        <div v-else class="alert alert-primary text-center" role="alert">
          {{sharing.sharing_state_machine.status.metadata.title}}
        </div>
      </div>
    </div>

    <div v-if="owner || joined">
      <div class="container mt-4">
        <div class="row">
          <div v-if="sharing.active_users.length" class="col-md-4">
            <h4>Membri del gruppo</h4>


            <member-item :user="sharing.owner" :sharing="sharing" :isAdmin="true"/>
            <div v-for="(user, index) in sharing.active_users" :key="index" class="media text-muted pt-3">
              <member-item :user="user" :sharing="sharing"/>
            </div>



            <!--<div class="mt-4">
              <hr>
              <a @click.prevent="leaveGroup" href="#" class="btn btn-outline-secondary btn-block">Abbandona gruppo</a>
              <hr>
              <small>Il prossimo rinnovo sarà il <strong>{{sharing.renewalInfo.renewalDate | moment("D MMMM YYYY")}}</strong></small>
              <hr>
              <small>Se vuoi chiedere un rimborso il giorno limite è il <strong>{{sharing.refundInfo.day_limit | moment("D MMMM YYYY")}}</strong></small>
            </div>
            -->


          </div>
          <div class="col-md-8">
            <h4>Chat del gruppo</h4>

            <div v-if="sharing.chats.length" class="mesgs">
              <div class="msg_history">
                <div v-for="chat in sharing.chats" :key="chat.id">
                  <div v-if="chat.user.id !== authUser.id" class="incoming_msg">
                    <div class="incoming_msg_img">
                      <img class="rounded-circle" :src="chat.user.photo_url">
                    </div>
                    <div class="received_msg">
                      <div class="received_withd_msg">
                        <p>{{ chat.message }}</p>
                        <span class="time_date"> 11:01 AM    |    June 9</span></div>
                    </div>
                  </div>
                  <div v-else class="outgoing_msg">
                    <div class="outgoing_msg_img">
                      <img class="rounded-circle" :src="chat.user.photo_url">
                    </div>
                    <div class="sent_msg">
                      <p>{{ chat.message }}</p>
                      <span class="time_date"> 11:01 AM    |    June 9</span> </div>
                  </div>
                </div>
              </div>
              <div class="type_msg">
                <div class="input_msg_write">
                  <input type="text" class="write_msg" placeholder="Type a message">
                  <button class="msg_send_btn" type="button"><i class="fa fa-paper-plane-o" aria-hidden="true"></i></button>
                </div>
              </div>
            </div>
            <h1 v-else>Nessun messaggio in chat</h1>
          </div>
        </div>
      </div>
    </div>

  </div>
</template>

<script>
import { mapGetters } from 'vuex'
import axios from 'axios'
import MemberItem from '~/components/MemberItem'

export default {
  middleware: 'auth',
  components: {
    MemberItem
  },

  created () {
    this.$store.dispatch('sharings/fetchSharing', this.$route.params.sharing_id)
  },

  computed: {
    ...mapGetters({
      sharing: 'sharings/sharing',
      authUser: 'auth/user'
    }),
    availability: function () {
      return this.sharing.availability > 0
    },
    owner: function () {
      return this.authUser.id === this.sharing.owner_id
    },
    foreign: function () {
      return this.sharing.sharing_state_machine === null
    },
    joined: function () {
      return this.sharing.sharing_state_machine && this.sharing.sharing_state_machine.status.value === 3
    },
  },

  methods: {

    joinGroup () {
      axios.post(`/api/sharings/${this.sharing.id}/join`).then((response) => {
        this.$store.dispatch('sharings/updateSharing', { sharing: response.data })
      })
    },

    doTransition (transition) {
      axios.patch(`/api/sharings/${this.sharing.id}/transitions/${transition}`).then((response) => {
        this.$store.dispatch('sharings/updateSharing', { sharing: response.data })
      })
    }
  }
}
</script>

<style scoped>
  .jumbotron{
    background-size: cover;
    background-repeat: no-repeat;
    background-position: center;
  }

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

  .msg_history {
    height: 516px;
    overflow-y: auto;
  }

</style>
