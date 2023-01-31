<template>
  <div id="chat-login-area">
    <div class="col-md-12">
      <div style="display: inline-flex">
        <div class="input-success">
          <div class="warning-msg"> All operators are busy at the moment, please leave a message. We will
            contact you ASAP!
          </div>
        </div>

      </div>
      <form @submit.prevent="onSubmit" id="guest-login-form" method="POST" autocomplete="off" novalidate=""
            style="height: 350px;">
        <div class="form-group mb-20">
          <label for="name">Name:</label>
          <input v-model="auth.username" type="text" name="name" class="form-control" id="name" required="">
        </div>
        <div class="form-group mb-20">
          <label for="name">Email:</label>
          <input v-model="auth.email" type="email" name="email" class="form-control" id="email" required="">
        </div>
        <div class="form-group mb-20">
          <label for="message">Message:</label>
          <textarea v-model="content" name="message" class="form-control" id="message"></textarea>
        </div>
        <div class="form-group">
          <button type="submit" class="btn btn-primary btn-start-chat btn-block">{{ online ? 'Start Chat' : 'Send Message' }}</button>
          <div v-if="alert.show">
            <div class="input-success">
              <div class="success-msg">{{ alert.message }}</div>
            </div>
          </div>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import {ref, reactive} from "vue";
// import useVuelidate from '@vuelidate/core'
// import {required, email} from '@vuelidate/validators'

const content = ref('')
const auth = reactive({
  username: '',
  email: '',
})

const alert = reactive({
  show: false,
  message: 'Success'
})

const emit = defineEmits(['onOffline'])

function onSubmit() {

    let newMessage =  {
      content: content.value,
    }


  emit('onOffline', newMessage, auth);
}


function showAlert(msg){
  alert.show = true
  setTimeout(() => alert.show = false, 2500)
}

function clearMessage(){
  content.value = ''
}

defineExpose({
  showAlert,
  clearMessage
})
</script>

<style scoped>
#chat-login-area {
  display: flex;
  align-items: flex-start;
  justify-content: center;
  height: 100%;
}

.form-group {
  position: relative;
  flex-direction: column;
  display: flex;
}

.form-group label {
  color: #fff;
}

.form-control {
  border: 1px solid #CCC;
  border-radius: 4px;
  padding: 10px;
  background-image: none;
  -webkit-transition: all 0.30s ease-in-out;
  -moz-transition: all 0.30s ease-in-out;
  -ms-transition: all 0.30s ease-in-out;
  -o-transition: all 0.30s ease-in-out;
}

.form-control:focus,
.form-control.focus {
  padding: 10px;
  border-radius: 4px;
  background-image: none !important;
  background-size: 100% 2px, 100% 1px;
  box-shadow: 0 0 5px rgba(81, 203, 238, 1);
  border: 1px solid rgba(81, 203, 238, 1);
}

.form-control:disabled {
  border: 1px solid #CCC;
  background: #DDD;
}

#guest-login-form {
  display: flex;
  flex-direction: column;
  justify-content: center;
}

.error-msg {
  color: red;
  position: absolute;
  font-weight: 800;
}

.success-msg {
  text-align: center;
  position: absolute;
  color: #00ff00;
  width: 100%;
  left: 0;
  padding: 5px 30px;
  font-size: 14px;
  background: linear-gradient(99.8deg, rgba(196, 47, 237, 0.5) 14.73%, rgba(99, 158, 255, 0.5) 88.93%);
}

.warning-msg {
  text-align: center;
  position: absolute;
  color: #00ff00;
  width: 100%;
  left: 0;
  top: 0;
  padding: 5px 30px;
  font-size: 14px;
  background: linear-gradient(99.8deg, rgba(196, 47, 237, 0.5) 14.73%, rgba(99, 158, 255, 0.5) 88.93%);
}
</style>
