<template>
  <div class="form">
    <div style="margin: 0 auto" class="profile-card qr-code">
      <div class="row mb-10">
        <span class="total-text">Total: {{ data.total }} {{ ps.code }}</span>
      </div>
      <div style="align-items: center" class="row mb-20 justify_center">
        <div class="mb-10" v-html="data.html">

        </div>
        <div class="col form__item-clipboard">
          <div class="d-flex info-block">
            <ul>
              <li>FOR THE BEST GAMING EXPERIENCE, YOUR DEPOSIT WILL AUTOMATICALLY SWAP TO USDT.
              </li>
              <li>Please note: You can buy crypto at <a class="link" href="https://www.binance.com/" target="_blank">Binance.com</a>
                with your credit card.
              </li>
            </ul>
          </div>
          <div class="d-flex justify_center mb-20">
            <div id="clipBoard" @click="copyClipboardCode('testing-code')" style="text-transform: none"
                 class="form__item address form__badge form__badge_primary col-auto">
              <span class="ellipsis">{{ data.address }}</span>
              <span class="indent">{{ data.address }}</span>
              <button class="button copy gradient clipboard-right">
                Copy Address
              </button>
            </div>

            <input @click="copyClipboardCode('testing-code')" type="hidden" id="testing-code"
                   :value="data.address">
          </div>
          <span class="info-block d-flex mb-20"> Send {{ data.total }} {{ ps.code }} (in ONE payment) to:</span>
          <div class="d-flex justify_center">
            <div id="clipTotal" @click="copyClipboardCode('clip-total')" style="text-transform: none"
                 class="form__item form-item_left form__badge form__badge_primary col-auto">
              {{ data.total }}
            </div>
            <button @click="copyClipboardCode('clip-total')" class="button gradient clipboard-right">
              Copy Total
            </button>
            <input @click="copyClipboardCode('clip-total')" type="hidden" id="clip-total"
                   :value="data.total">
          </div>
        </div>
      </div>
      <div style="width: 100%" class="row awaiting-container">
        <button class="button">
                                   <span class="d-flex button-icon">
                                       <svg xmlns="http://www.w3.org/2000/svg"
                                            xmlns:xlink="http://www.w3.org/1999/xlink"
                                            style="background: none; display: block; shape-rendering: auto;"
                                            width="30px" height="30px" viewBox="0 0 100 100"
                                            preserveAspectRatio="xMidYMid">
                                            <circle cx="50" cy="50" r="32" stroke-width="8" stroke="#fe718d"
                                                    stroke-dasharray="50.26548245743669 50.26548245743669"
                                                    fill="none" stroke-linecap="round">
                                              <animateTransform attributeName="transform" type="rotate"
                                                                repeatCount="indefinite" dur="1s" keyTimes="0;1"
                                                                values="0 50 50;360 50 50"></animateTransform>
                                            </circle>
                                         <!-- [ldio] generated by https://loading.io/ --></svg>
                                       Awaiting Payment From You</span>
        </button>
        <button class="button gradient" @click="cancelDeposit">Cancel</button>
      </div>
    </div>
  </div>
</template>

<script>
import $ from 'jquery';

export default {
  name: "NewDepositCardComponent",
  props: ['data', 'ps'],
  emits: ['cancelDeposit'],
  methods: {
    copyClipboardCode(id) {
      let testingCodeToCopy = document.querySelector(`#${id}`);
      testingCodeToCopy.setAttribute('type', 'text')
      testingCodeToCopy.select()

      try {
        let successful = document.execCommand('copy');
        let msg = successful ? 'successful' : 'unsuccessful';
        this.$toast.success(`Clipboard: ${msg}`, {
          position: "top-right",
        });
      } catch (err) {
        this.$toast.error('Oops, unable to copy', {
          position:
              "top-right",
        });
      }

      testingCodeToCopy.setAttribute('type', 'hidden')
      window.getSelection().removeAllRanges()
    },
    cancelDeposit() {
      this.$emit('cancelDeposit')
    }
  },
}
</script>

<style lang="scss" scoped>
.address {
  padding-right: 125px;
  border-radius: 4px 4px 4px 4px;
}

.button {
  padding: 12px 16px;
  border-radius: 4px;
  font-weight: bold;
  z-index: 1;
  color: white;

  &.gradient {
    background: linear-gradient(135deg, #FF6A28, #FE2F57, #FE2F57, #FF6A28);
    background-size: 300%;
  }

  &.copy {
    height: 100%;
    position: absolute;
    right: -2px;
    top: 0;
  }

  &.light {
    background: linear-gradient(99.8deg, #c42fed, #639eff, #639eff, #c42fed);
    background-size: 300%;
    @media (max-width: 460px) {
      width: 100%;
    }
  }
}

.qr-code {
  display: flex;
  flex-direction: column;
  height: 100%;
}

.awaiting-container {
  display: flex;
  justify-content: center;

  @media (max-width: 460px) {
    height: 150px;
    justify-content: space-between;
    align-items: center;
  }
}

.clipboard-left {
  border-radius: 4px 0 0 4px;
}

.clipboard-right {
  border-radius: 0 4px 4px 0;
}

.total-text {
  font-size: 24px;
  color: white;
  width: 100%;
  text-align: end;
  font-weight: 700;
  font-feature-settings: 'pnum' on, 'lnum' on;
}

.info-block {
  color: white;
  justify-content: center;
  align-items: center;
  flex-direction: column;
  margin-bottom: 10px;
  font-weight: 500;
}

.form__item {
  position: relative;
}

.form__item-clipboard {
  justify-content: center;
  align-items: center;
  width: 100%;
  padding-left: 24px;
}


.button-icon {
  display: flex;
  justify-content: center;
  align-items: center;
}

.button-icon svg {
  margin-right: 10px;
}


.awaiting-container button:first-child {
  margin: auto auto auto 0;
}

.profile-card {
  width: 100%;
  max-width: 870px;
}

.text-decoration-underline {
  text-decoration: underline;
}

.ucfirst {
  display: inline-block;
  text-transform: lowercase;
}

.ucfirst::first-letter {
  text-transform: uppercase;
}

.link {
  color: #FF6A28;
}

#clipBoard {
  display: inline-block;
  vertical-align: bottom;
  white-space: nowrap;
  width: 100%;
  max-width: 420px;
  overflow: hidden;
  & > span {
    white-space: nowrap;
    overflow: hidden;
    vertical-align:middle;
  }
}

.ellipsis {
  display: inline-block;
  width: 100%;
  text-overflow: ellipsis;
  @media (max-width: 520px) {
    width: calc(50% + 1.2em);
  }
}

.indent {
  display: none;
  width: calc(50% - 1.2em);
  justify-content: flex-end;
  @media (max-width: 520px) {
    display: inline-flex;
  }
}




</style>
