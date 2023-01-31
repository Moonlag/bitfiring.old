<template>
    <div :class="{ 'navbar__scroll': showNavbar }" class="navbar container flex_center">

        <a @click.prevent="goHome" class="navbar__logo" href="">
            <picture>
                <source :srcset="`${$cdn}/assets/img/navbar/logo.svg`" type="image/webp">
                <img class="img_fluid" :src="`${$cdn}/assets/img/navbar/logo.svg`" alt="crypto bitcoin casino"
                     title="crypto casino" width="205" height="126">
            </picture>
        </a>

        <div :class="{'nav__toggle': toggleNavbar}" class="navbar__box">
            <router-link @click="closeNavbar" class="navbar__link link-nav" :to="{name: `providers`, params: {locale}}">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24"
                     class="link-nav--icon stroke" fill="none">
                    <path
                        d="M20.7669 6.4668H2.90032C1.66689 6.4668 0.666992 7.46669 0.666992 8.70012V19.8667C0.666992 21.1002 1.66689 22.1001 2.90032 22.1001H20.7669C22.0004 22.1001 23.0002 21.1002 23.0002 19.8667V8.70012C23.0002 7.46669 22.0004 6.4668 20.7669 6.4668Z"
                        stroke-linecap="round" stroke-linejoin="round"/>
                    <path
                        d="M16.3005 22.0999V4.23333C16.3005 3.64101 16.0652 3.07296 15.6464 2.65413C15.2275 2.2353 14.6595 2 14.0672 2H9.60051C9.0082 2 8.44014 2.2353 8.02131 2.65413C7.60248 3.07296 7.36719 3.64101 7.36719 4.23333V22.0999"
                        stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <div class="link-nav__text">
                    {{ $t('header_providers') }}
                </div>
            </router-link>
            <router-link @click="closeNavbar" class="navbar__link link-nav" :to="{name: 'bonuses', params: {locale}}">
                <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24"
                     class="link-nav--icon fill">
                    <path fill-rule="evenodd" clip-rule="evenodd"
                          d="M8.11849 3.00032C7.32813 3.00032 6.6604 3.66805 6.6604 4.45841C6.6604 5.26996 7.28578 5.91036 8.08414 5.91625H11.3189L11.1962 5.63693C10.1885 3.34287 8.83542 3.00032 8.11849 3.00032ZM15.9051 3.00036C15.1838 3.00036 13.8245 3.34295 12.8241 5.63745L12.7026 5.91625H15.9377C16.7376 5.91036 17.3631 5.27 17.3632 4.45845C17.3632 3.66809 16.6955 3.00036 15.9051 3.00036ZM3.00036 10.4857H11.5115V6.91685H3.00036V10.4857ZM12.5118 10.4857H20.9996L20.9996 6.91685H12.5118V10.4857ZM4.30586 20.2304H11.5115V11.4858H4.30586V20.2304ZM12.5118 20.2304H19.7175V11.4858H12.5118V20.2304ZM3.80566 21.2307C3.52988 21.2307 3.3055 21.0063 3.3055 20.7305V11.4858H2.50016C2.22438 11.4858 2 11.2614 2 10.9857V6.41641C2 6.14064 2.22438 5.91625 2.50016 5.91625H6.10821L5.95127 5.62303C5.76076 5.26713 5.66004 4.86442 5.66004 4.45841C5.66004 3.10283 6.76287 2 8.11845 2C10.0214 2 11.1903 3.44606 11.8358 4.6592L12.0116 4.98972L12.1875 4.6592C12.833 3.4461 14.0019 2 15.9048 2C16.5604 2 17.1774 2.25602 17.6423 2.72092C18.1072 3.18582 18.3632 3.80291 18.3632 4.45845C18.3632 4.86442 18.2625 5.26713 18.072 5.62307L17.9151 5.91625H21.4998C21.6335 5.91625 21.759 5.96829 21.8535 6.06271C21.948 6.15717 22 6.28279 22 6.41637V10.9857C22 11.2614 21.7756 11.4858 21.4998 11.4858H20.7178V20.7305C20.7178 21.0063 20.4934 21.2307 20.2176 21.2307H3.80566Z"
                          fill="white"/>
                </svg>
                <div class="link-nav__text">
                    {{$t('header_bonuses')}}
                </div>
            </router-link>
            <router-link @click="closeNavbar" class="navbar__link link-nav" :to="{name: 'leaderboard', params: {locale}}">
                <svg width="28" height="28" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" class="link-nav--icon fill">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M19.5312 5.15479H17.478C17.4855 4.92737 17.4896 4.69861 17.4896 4.46875C17.4896 4.20984 17.2797 4 17.0209 4H6.97913C6.72034 4 6.51038 4.20984 6.51038 4.46875C6.51038 4.69861 6.51453 4.92737 6.52197 5.15479H4.46875C4.20984 5.15479 4 5.36462 4 5.62354C4 7.72388 4.54895 9.70544 5.54565 11.2034C6.53088 12.6842 7.84326 13.5336 9.26099 13.6162C9.5824 13.9659 9.92212 14.2511 10.276 14.4686V16.552H9.48962C8.53894 16.552 7.76562 17.3254 7.76562 18.276V19.0624H7.7323C7.47339 19.0624 7.26355 19.2723 7.26355 19.5311C7.26355 19.79 7.47339 19.9999 7.7323 19.9999H16.2677C16.5266 19.9999 16.7365 19.79 16.7365 19.5311C16.7365 19.2723 16.5266 19.0624 16.2677 19.0624H16.2344V18.276C16.2344 17.3254 15.4611 16.552 14.5104 16.552H13.724V14.4686C14.0779 14.2512 14.4177 13.9659 14.7391 13.6162C16.1567 13.5336 17.4691 12.6842 18.4545 11.2034C19.4512 9.70544 20 7.72388 20 5.62354C20 5.36462 19.7902 5.15479 19.5312 5.15479ZM6.32617 10.6841C5.50403 9.44861 5.021 7.83264 4.94739 6.09229H6.57434C6.74341 8.23169 7.24561 10.2092 8.03052 11.7789C8.15552 12.0289 8.28625 12.2651 8.42188 12.4875C7.64221 12.1931 6.92065 11.5776 6.32617 10.6841ZM15.2969 18.276V19.0625H8.70312V18.276C8.70312 17.8424 9.05591 17.4895 9.48962 17.4895H14.5104C14.9441 17.4895 15.2969 17.8424 15.2969 18.276ZM12.7865 16.552H11.2135V14.876C11.4712 14.9438 11.7338 14.9791 12 14.9791C12.2662 14.9791 12.5288 14.9438 12.7865 14.876V16.552ZM13.0768 13.7607C13.0557 13.7694 13.0354 13.7799 13.016 13.7915C12.6847 13.9563 12.3442 14.0416 12 14.0416C11.6559 14.0416 11.3156 13.9563 10.9844 13.7917C10.9647 13.7799 10.9443 13.7694 10.923 13.7605C10.5554 13.5662 10.1998 13.2727 9.86353 12.8871C9.84583 12.8618 9.82593 12.8385 9.80383 12.8173C9.46997 12.4209 9.15588 11.9333 8.86902 11.3596C8.00757 9.63684 7.51001 7.37158 7.45337 4.9375H16.5466C16.4899 7.37158 15.9923 9.63696 15.131 11.3596C14.8441 11.9333 14.53 12.4209 14.1963 12.8173C14.1741 12.8385 14.1539 12.8619 14.1364 12.8872C13.8 13.2729 13.4443 13.5663 13.0768 13.7607ZM17.6738 10.6841C17.0793 11.5776 16.3578 12.1931 15.5781 12.4875C15.7137 12.2651 15.8445 12.0289 15.9695 11.7789C16.7544 10.2092 17.2565 8.23169 17.4257 6.09229H19.0526C18.979 7.83264 18.496 9.44861 17.6738 10.6841Z" fill="white"/>
                </svg>
                <div class="link-nav__text">
                    Leaderboard
                </div>
            </router-link>
            <button @click.prevent="install" v-if="deferredPrompt" type="submit" class="navbar__link link-nav">
                <svg width="24" height="24" class="link-nav--icon fill" xmlns="http://www.w3.org/2000/svg"
                     viewBox="0 0 32 32">
                    <path
                        d="M27.5,12A2.5,2.5,0,0,0,25,14.5V13a1,1,0,0,0-1-1H8a1,1,0,0,0-1,1v1.5a2.5,2.5,0,0,0-5,0v6a2.5,2.5,0,0,0,5,0V24a1,1,0,0,0,1,1h2v4.5a2.5,2.5,0,0,0,5,0V25h2v4.5a2.5,2.5,0,0,0,5,0V25h2a1,1,0,0,0,1-1V20.5a2.5,2.5,0,0,0,5,0v-6A2.5,2.5,0,0,0,27.5,12ZM5,20.5a.5.5,0,0,1-1,0v-6a.5.5,0,0,1,1,0Zm8,9a.5.5,0,0,1-1,0V25h1Zm6.5.5a.51.51,0,0,1-.5-.5V25h1v4.5A.51.51,0,0,1,19.5,30ZM23,23H9V14H23Zm5-2.5a.5.5,0,0,1-1,0v-6a.5.5,0,0,1,1,0Z"></path>
                    <path
                        d="M7.08,9.88a1,1,0,0,0,.24.78,1,1,0,0,0,.75.34H23.93a1,1,0,0,0,.75-.34,1,1,0,0,0,.24-.78A9,9,0,0,0,21.65,4l2.18-2.18A1,1,0,0,0,22.42.42L19.93,2.91A9,9,0,0,0,16,2a8.87,8.87,0,0,0-4.42,1.18L8.82.42A1,1,0,0,0,7.4.42a1,1,0,0,0,0,1.41L9.94,4.37A8.94,8.94,0,0,0,7.08,9.88ZM22.7,9H9.3A7,7,0,0,1,22.7,9Z"></path>
                </svg>
                <div class="link-nav__text">{{$t('header_pwa')}}</div>
            </button>
            <div class="navbar__link link-nav link-lang">
                <LanguageComponent/>
            </div>
        </div>

        <div class="navbar__right">
            <div class="navbar__wheel" @click="open_lucky">

                <svg width="56" height="56" viewBox="0 0 56 56" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <g id="wheel">
                        <g filter="url(#filter0_d_814_37897)">
                            <circle cx="28" cy="28" r="16" fill="#FF00E6"/>
                        </g>
                        <path d="M24.3831 14.4753C27.9701 13.5161 31.7912 14.021 35.0059 15.8791L28 28L24.3831 14.4753Z"
                              fill="url(#paint0_radial_814_37897)"/>
                        <path
                            d="M35.0049 15.8785C38.2198 17.7364 40.5649 20.7952 41.5244 24.3821L28 28L35.0049 15.8785Z"
                            fill="url(#paint1_radial_814_37897)"/>
                        <path d="M41.5265 24.3898C42.484 27.9773 41.9771 31.7982 40.1175 35.0119L28 28L41.5265 24.3898Z"
                              fill="url(#paint2_radial_814_37897)"/>
                        <path
                            d="M40.1178 35.0113C39.197 36.6027 37.9719 37.9971 36.5123 39.1149C35.0526 40.2328 33.3871 41.0522 31.6108 41.5263L28 28L40.1178 35.0113Z"
                            fill="url(#paint3_radial_814_37897)"/>
                        <path
                            d="M15.8732 21.0042C14.9545 22.5967 14.3585 24.3546 14.1191 26.1774C13.8798 28.0003 14.0018 29.8524 14.4783 31.6281L28 28L15.8732 21.0042Z"
                            fill="url(#paint4_radial_814_37897)"/>
                        <path
                            d="M14.4787 31.6298C14.9554 33.4054 15.7771 35.0698 16.897 36.5278C18.0169 37.9859 19.413 39.2091 21.0057 40.1276L28 28L14.4787 31.6298Z"
                            fill="url(#paint5_radial_814_37897)"/>
                        <path
                            d="M15.8746 21.0019C16.7974 19.4029 18.0274 18.0023 19.4936 16.8806C20.9599 15.7589 22.6336 14.9383 24.4182 14.4659L28 28L15.8746 21.0019Z"
                            fill="url(#paint6_radial_814_37897)"/>
                        <path d="M20.999 40.1238C24.2144 41.9805 28.0358 42.4839 31.6224 41.5232L28 28L20.999 40.1238Z"
                              fill="url(#paint7_radial_814_37897)"/>
                        <g filter="url(#filter1_f_814_37897)">
                            <circle cx="28" cy="28" r="12.3481" stroke="white"/>
                        </g>
                        <circle cx="36.5" cy="15.5" r="0.5" fill="white"/>
                        <circle cx="41.5" cy="34.5" r="0.5" fill="white"/>
                        <circle cx="21.5" cy="41.5" r="0.5" fill="white"/>
                        <circle cx="14.5" cy="21.5" r="0.5" fill="white"/>
                    </g>
                    <g filter="url(#filter2_d_814_37897)">
                        <ellipse cx="28.3343" cy="28.3343" rx="4.33429" ry="4.33429"
                                 fill="url(#paint8_radial_814_37897)"/>
                    </g>
                    <g filter="url(#filter7_d_814_37897)">
                        <path d="M36 28L44 23V33L36 28Z" fill="#EDCE13"/>
                        <path d="M43 25L38 28H41.5L43 25Z" fill="url(#paint9_linear_814_37897)"/>
                        <path d="M41.5 28L43 31V25L41.5 28Z" fill="url(#paint10_linear_814_37897)"/>
                        <path d="M41.5 28H38L43 31L41.5 28Z" fill="url(#paint11_linear_814_37897)"/>
                    </g>
                    <defs>
                        <filter id="filter0_d_814_37897" x="0" y="0" width="56" height="56" filterUnits="userSpaceOnUse"
                                color-interpolation-filters="sRGB">
                            <feFlood flood-opacity="0" result="BackgroundImageFix"/>
                            <feColorMatrix in="SourceAlpha" type="matrix"
                                           values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0"
                                           result="hardAlpha"/>
                            <feOffset/>
                            <feGaussianBlur stdDeviation="6"/>
                            <feComposite in2="hardAlpha" operator="out"/>
                            <feColorMatrix type="matrix"
                                           values="0 0 0 0 1 0 0 0 0 0.0745098 0 0 0 0 0.909804 0 0 0 0.6 0"/>
                            <feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow_814_37897"/>
                            <feBlend mode="normal" in="SourceGraphic" in2="effect1_dropShadow_814_37897"
                                     result="shape"/>
                        </filter>
                        <filter id="filter1_f_814_37897" x="5.15192" y="5.15188" width="45.6962" height="45.6962"
                                filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
                            <feFlood flood-opacity="0" result="BackgroundImageFix"/>
                            <feBlend mode="normal" in="SourceGraphic" in2="BackgroundImageFix" result="shape"/>
                            <feGaussianBlur stdDeviation="5" result="effect1_foregroundBlur_814_37897"/>
                        </filter>
                        <filter id="filter2_d_814_37897" x="22" y="22" width="12.6686" height="12.6686"
                                filterUnits="userSpaceOnUse"
                                color-interpolation-filters="sRGB">
                            <feFlood flood-opacity="0" result="BackgroundImageFix"/>
                            <feColorMatrix in="SourceAlpha" type="matrix"
                                           values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0"
                                           result="hardAlpha"/>
                            <feOffset/>
                            <feGaussianBlur stdDeviation="1"/>
                            <feComposite in2="hardAlpha" operator="out"/>
                            <feColorMatrix type="matrix"
                                           values="0 0 0 0 0.1125 0 0 0 0 0.00421875 0 0 0 0 0.101992 0 0 0 0.6 0"/>
                            <feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow_814_37897"/>
                            <feBlend mode="normal" in="SourceGraphic" in2="effect1_dropShadow_814_37897"
                                     result="shape"/>
                        </filter>
                        <filter id="filter3_d_814_37897" x="38" y="20" width="9" height="9" filterUnits="userSpaceOnUse"
                                color-interpolation-filters="sRGB">
                            <feFlood flood-opacity="0" result="BackgroundImageFix"/>
                            <feColorMatrix in="SourceAlpha" type="matrix"
                                           values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0"
                                           result="hardAlpha"/>
                            <feMorphology radius="2" operator="dilate" in="SourceAlpha"
                                          result="effect1_dropShadow_814_37897"/>
                            <feOffset/>
                            <feGaussianBlur stdDeviation="1"/>
                            <feComposite in2="hardAlpha" operator="out"/>
                            <feColorMatrix type="matrix" values="0 0 0 0 1 0 0 0 0 1 0 0 0 0 1 0 0 0 0.5 0"/>
                            <feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow_814_37897"/>
                            <feBlend mode="normal" in="SourceGraphic" in2="effect1_dropShadow_814_37897"
                                     result="shape"/>
                        </filter>
                        <filter id="filter4_d_814_37897" x="30" y="37" width="9" height="9" filterUnits="userSpaceOnUse"
                                color-interpolation-filters="sRGB">
                            <feFlood flood-opacity="0" result="BackgroundImageFix"/>
                            <feColorMatrix in="SourceAlpha" type="matrix"
                                           values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0"
                                           result="hardAlpha"/>
                            <feMorphology radius="2" operator="dilate" in="SourceAlpha"
                                          result="effect1_dropShadow_814_37897"/>
                            <feOffset/>
                            <feGaussianBlur stdDeviation="1"/>
                            <feComposite in2="hardAlpha" operator="out"/>
                            <feColorMatrix type="matrix" values="0 0 0 0 1 0 0 0 0 1 0 0 0 0 1 0 0 0 0.5 0"/>
                            <feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow_814_37897"/>
                            <feBlend mode="normal" in="SourceGraphic" in2="effect1_dropShadow_814_37897"
                                     result="shape"/>
                        </filter>
                        <filter id="filter5_d_814_37897" x="20" y="9" width="9" height="9" filterUnits="userSpaceOnUse"
                                color-interpolation-filters="sRGB">
                            <feFlood flood-opacity="0" result="BackgroundImageFix"/>
                            <feColorMatrix in="SourceAlpha" type="matrix"
                                           values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0"
                                           result="hardAlpha"/>
                            <feMorphology radius="2" operator="dilate" in="SourceAlpha"
                                          result="effect1_dropShadow_814_37897"/>
                            <feOffset/>
                            <feGaussianBlur stdDeviation="1"/>
                            <feComposite in2="hardAlpha" operator="out"/>
                            <feColorMatrix type="matrix" values="0 0 0 0 1 0 0 0 0 1 0 0 0 0 1 0 0 0 0.5 0"/>
                            <feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow_814_37897"/>
                            <feBlend mode="normal" in="SourceGraphic" in2="effect1_dropShadow_814_37897"
                                     result="shape"/>
                        </filter>
                        <filter id="filter6_d_814_37897" x="9" y="27" width="9" height="9" filterUnits="userSpaceOnUse"
                                color-interpolation-filters="sRGB">
                            <feFlood flood-opacity="0" result="BackgroundImageFix"/>
                            <feColorMatrix in="SourceAlpha" type="matrix"
                                           values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0"
                                           result="hardAlpha"/>
                            <feMorphology radius="2" operator="dilate" in="SourceAlpha"
                                          result="effect1_dropShadow_814_37897"/>
                            <feOffset/>
                            <feGaussianBlur stdDeviation="1"/>
                            <feComposite in2="hardAlpha" operator="out"/>
                            <feColorMatrix type="matrix" values="0 0 0 0 1 0 0 0 0 1 0 0 0 0 1 0 0 0 0.5 0"/>
                            <feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow_814_37897"/>
                            <feBlend mode="normal" in="SourceGraphic" in2="effect1_dropShadow_814_37897"
                                     result="shape"/>
                        </filter>
                        <filter id="filter7_d_814_37897" x="33" y="21" width="12" height="14"
                                filterUnits="userSpaceOnUse"
                                color-interpolation-filters="sRGB">
                            <feFlood flood-opacity="0" result="BackgroundImageFix"/>
                            <feColorMatrix in="SourceAlpha" type="matrix"
                                           values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0"
                                           result="hardAlpha"/>
                            <feOffset dx="-1"/>
                            <feGaussianBlur stdDeviation="1"/>
                            <feComposite in2="hardAlpha" operator="out"/>
                            <feColorMatrix type="matrix"
                                           values="0 0 0 0 0.2 0 0 0 0 0.0183333 0 0 0 0 0.181542 0 0 0 0.5 0"/>
                            <feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow_814_37897"/>
                            <feBlend mode="normal" in="SourceGraphic" in2="effect1_dropShadow_814_37897"
                                     result="shape"/>
                        </filter>
                        <radialGradient id="paint0_radial_814_37897" cx="0" cy="0" r="1" gradientUnits="userSpaceOnUse"
                                        gradientTransform="translate(28 28) rotate(-90) scale(14)">
                            <stop stop-color="#8F0081"/>
                            <stop offset="0.914694" stop-color="#FF20E9"/>
                            <stop offset="1" stop-color="#500152"/>
                        </radialGradient>
                        <radialGradient id="paint1_radial_814_37897" cx="0" cy="0" r="1" gradientUnits="userSpaceOnUse"
                                        gradientTransform="translate(28 28) rotate(-90) scale(14)">
                            <stop stop-color="#D1A301"/>
                            <stop offset="0.914734" stop-color="#FFE601"/>
                            <stop offset="1" stop-color="#BFAC00"/>
                        </radialGradient>
                        <radialGradient id="paint2_radial_814_37897" cx="0" cy="0" r="1" gradientUnits="userSpaceOnUse"
                                        gradientTransform="translate(28 28) rotate(-90) scale(14)">
                            <stop stop-color="#8F0081"/>
                            <stop offset="0.914694" stop-color="#FF20E9"/>
                            <stop offset="1" stop-color="#500152"/>
                        </radialGradient>
                        <radialGradient id="paint3_radial_814_37897" cx="0" cy="0" r="1" gradientUnits="userSpaceOnUse"
                                        gradientTransform="translate(28 28) rotate(-90) scale(14)">
                            <stop stop-color="#D1A301"/>
                            <stop offset="0.914734" stop-color="#FFE601"/>
                            <stop offset="1" stop-color="#BFAC00"/>
                        </radialGradient>
                        <radialGradient id="paint4_radial_814_37897" cx="0" cy="0" r="1" gradientUnits="userSpaceOnUse"
                                        gradientTransform="translate(28 28) rotate(90) scale(14)">
                            <stop stop-color="#8F0081"/>
                            <stop offset="0.914694" stop-color="#FF20E9"/>
                            <stop offset="1" stop-color="#500152"/>
                        </radialGradient>
                        <radialGradient id="paint5_radial_814_37897" cx="0" cy="0" r="1" gradientUnits="userSpaceOnUse"
                                        gradientTransform="translate(28 28) rotate(90) scale(14)">
                            <stop stop-color="#D1A301"/>
                            <stop offset="0.914734" stop-color="#FFE601"/>
                            <stop offset="1" stop-color="#BFAC00"/>
                        </radialGradient>
                        <radialGradient id="paint6_radial_814_37897" cx="0" cy="0" r="1" gradientUnits="userSpaceOnUse"
                                        gradientTransform="translate(28 28) rotate(90) scale(14)">
                            <stop stop-color="#D1A301"/>
                            <stop offset="0.914734" stop-color="#FFE601"/>
                            <stop offset="1" stop-color="#BFAC00"/>
                        </radialGradient>
                        <radialGradient id="paint7_radial_814_37897" cx="0" cy="0" r="1" gradientUnits="userSpaceOnUse"
                                        gradientTransform="translate(28 28) rotate(90) scale(14)">
                            <stop stop-color="#8F0081"/>
                            <stop offset="0.914694" stop-color="#FF20E9"/>
                            <stop offset="1" stop-color="#500152"/>
                        </radialGradient>
                        <radialGradient id="paint8_radial_814_37897" cx="0" cy="0" r="1" gradientUnits="userSpaceOnUse"
                                        gradientTransform="translate(28.3343 28.3343) rotate(90) scale(4.33429)">
                            <stop offset="0.723936" stop-color="white"/>
                            <stop offset="1" stop-color="#CFAED2"/>
                        </radialGradient>
                        <linearGradient id="paint9_linear_814_37897" x1="43" y1="25" x2="38" y2="28"
                                        gradientUnits="userSpaceOnUse">
                            <stop stop-color="#7A1C78"/>
                            <stop offset="1" stop-color="#B50AAD"/>
                        </linearGradient>
                        <linearGradient id="paint10_linear_814_37897" x1="42.25" y1="25" x2="42.25" y2="31"
                                        gradientUnits="userSpaceOnUse">
                            <stop stop-color="#FF15E9"/>
                            <stop offset="1" stop-color="#FF1EEA"/>
                        </linearGradient>
                        <linearGradient id="paint11_linear_814_37897" x1="38" y1="28" x2="43" y2="31"
                                        gradientUnits="userSpaceOnUse">
                            <stop stop-color="#F010DC"/>
                            <stop offset="1" stop-color="#BB01A9"/>
                        </linearGradient>
                    </defs>
                </svg>

            </div>
            <app-profile ref="profile" v-if="loggedIn"></app-profile>
            <app-auth v-else @open_pop="open_pop"></app-auth>
            <div class="navbar__user user-nav user-lang">
                <LanguageComponent/>
            </div>
        </div>

        <div @click="openNavbar" :class="{'aside-toggler__toggle': toggleNavbar}"
             class="aside-toggler navbar__open col-auto">
            <span></span>
            <span class="aside-toggler__middle"></span>
            <span></span>
        </div>

        <!--    <teleport to="body" v-if="luckyIsOpen">-->
        <!--        <fancybox-container>-->
        <!--          <div @click.stop class="popup fancybox-content" style=" display: inline-block;">-->
        <!--            <div class="popup-card">-->
        <!--              <div class="popup-card__main">-->
        <!--                <picture @click="close_lucky">-->
        <!--                  <source :srcset="`${$cdn}/assets/img/common/close-popup.svg`" type="image/webp">-->
        <!--                  <img style="z-index: 10; top: 15px; right: 15px" class="popup__close" :src="`${$cdn}/assets/img/common/close-popup.svg`" alt="close">-->
        <!--                </picture>-->
        <!--                <div class="background" style="display: flex; justify-content: center; align-items: center;">-->
        <!--                  <Lucky @onClose="close_lucky" @onFreespin="onFreespin"/>-->
        <!--                </div>-->
        <!--              </div>-->
        <!--            </div>-->
        <!--          </div>-->
        <!--        </fancybox-container>-->
        <!--    </teleport>-->

    </div>
</template>

<script setup>
import AppAuth from "./part/AppAuth.vue";
import AppProfile from "./part/AppProfile.vue";
import LanguageComponent from "../lang/LanguageComponent.vue";
import SelectComponent from "../lang/SelectComponent.vue";
import FancyboxContainer from "../../components/pop/FancyboxContainer.vue";
import {useI18n} from "vue-i18n";
// import Lucky from "../../components/lucky/App.vue";

import {gsap, Back, Circ} from "gsap";
import {ref, onBeforeUnmount, onMounted, computed} from "vue";
import {useStore} from "vuex";
import {useRouter} from "vue-router";

const profile = ref()
const deferredPrompt = ref(false)
const rad = ref(0)
const showNavbar = ref(false)
const toggleNavbar = ref(false)
const router = useRouter()
const store = useStore()
const i18n = useI18n({useScope: 'global'})

const {locale} = i18n

const loggedIn = computed(() => store.state.user.status.loggedIn)
const luckyConfig = computed(() => store.state.lucky.config)
const luckyIsOpen = computed(() => store.state.lucky.isOpen || false)

onMounted(() => {

    window.addEventListener("beforeinstallprompt", e => {
        e.preventDefault();
        // Stash the event so it can be triggered later.
        deferredPrompt.value = e;
    });

    window.addEventListener("appinstalled", () => {
        deferredPrompt.value = null;
    });

    window.addEventListener('scroll', onScroll)
    const wheel_tl = gsap.timeline({yoyo: true, repeat: -1});
    wheel_tl.fromTo("#wheel", {
        rotation: 0,
        transformOrigin: "center"
    }, {duration: 1.5, rotation: 360, transformOrigin: "center", repeat: -1, ease: 'linear'});
})

function onScroll() {
    const currentScrollPosition = window.scrollTop || document.documentElement.scrollTop
    showNavbar.value = currentScrollPosition > 0
}

function goHome() {
    if (router.currentRoute.value.name === 'landing') {
        return window.location.href = '/';
    } else if (router.currentRoute.value.name !== 'home') {
        return router.push({name: 'home', locale})
    }
    window.location.href = '/';
}

function open_pop(id) {
    store.commit('open_pop', id)
}

function openNavbar() {
    toggleNavbar.value = !toggleNavbar.value;
    classNavbar()
}

function closeNavbar() {
    console.log()
    toggleNavbar.value = false;
    classNavbar()
}

function classNavbar() {
    const el = document.body;
    if (toggleNavbar.value) {
        el.classList.add('body-toggle');
    } else {
        el.classList.remove('body-toggle');
    }
}

function close_lucky() {
    const el = document.body;
    el.classList.remove('fancybox-active', 'compensate-for-scrollbar');
    store.commit('lucky/CLOSE')
}

function open_lucky() {
    const el = document.body;
    el.classList.add('fancybox-active', 'compensate-for-scrollbar');
    store.commit('lucky/OPEN')
}

async function onFreespin(data) {
    if (profile.value) {
        await profile.value.onFreespin(data)
    }
}

async function install() {
    deferredPrompt.value.prompt();
}

onBeforeUnmount(() => {
    window.removeEventListener('scroll', onScroll)
})

</script>

<style lang="scss" scoped>


.navbar__wheel {
    margin-right: 16px;
    cursor: pointer;
    @media all and (max-width: 767.98px) {
        margin-right: 3px;
    }
}

.link-lang{
    display: none;
    @media all and (max-width: 767.98px) {
        display: block;
    }
}

.user-lang{
    display: block;
    @media all and (max-width: 767.98px) {
        display: none;
    }
}

.background {
    backdrop-filter: blur(16px);
    background: linear-gradient(99.8deg, rgba(130, 13, 163, .85) 14.73%, rgba(68, 27, 155, .85) 88.93%);
    border-radius: 8px;
}

.link-nav {
    &:hover .link-nav--icon {
        &.stroke {
            stroke: #DC5EFF;
            & path {
                stroke: #DC5EFF;
            }
        }

        &.fill {
            fill: #DC5EFF;
            & path {
                fill: #DC5EFF;
            }
        }
    }

    & .link-nav--icon {
        margin-right: 8px;
        padding-bottom: 2px;


        &.stroke {
            fill: transparent;
            stroke: #fff;


        }


        &.fill {
            fill: #fff;
            stroke: transparent;
        }

    }
}

</style>
