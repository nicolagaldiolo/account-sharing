function page (path) {
  return () => import(/* webpackChunkName: '' */ `~/pages/${path}`).then(m => m.default || m)
}

export default [
  { path: '/', name: 'welcome', component: page('welcome.vue') },

  { path: '/login', name: 'login', component: page('auth/login.vue') },
  { path: '/register', name: 'register', component: page('auth/register.vue') },
  { path: '/password/reset', name: 'password.request', component: page('auth/password/email.vue') },
  { path: '/password/reset/:token', name: 'password.reset', component: page('auth/password/reset.vue') },
  { path: '/email/verify/:id', name: 'verification.verify', component: page('auth/verification/verify.vue') },
  { path: '/email/resend', name: 'verification.resend', component: page('auth/verification/resend.vue') },

  { path: '/home', name: 'home', component: page('home.vue') },

  { path: '/categories', name: 'categories', component: page('home.vue') },
  { path: '/category/:category_id', name: 'category.show', component: page('categories/show.vue') },
  { path: '/category/:category_id/sharing/:sharing_id', name: 'sharing.show', component: page('sharings/show.vue') },
  { path: '/category/:category_id/sharing/:sharing_id/checkout', name: 'sharing.checkout', component: page('sharings/checkout.vue') },

  { path: '/sharing/create', name: 'sharing.create', component: page('sharings/create.vue') },
  { path: '/sharing/create/:category_id', name: 'sharing.create.category', component: page('sharings/createForm.vue') },

  { path: '/sharings',
    component: page('sharings/index.vue'),
    children: [
      { path: '', name: 'sharings', redirect: { name: 'sharings.pending' } },
      { path: 'pending', name: 'sharings.pending', component: page('sharings/lists.vue'), props: { type: 'pending', title: 'In attesa' } },
      { path: 'approved', name: 'sharings.approved', component: page('sharings/lists.vue'), props: { type: 'approved', title: 'Approvate' } },
      { path: 'joined', name: 'sharings.joined', component: page('sharings/lists.vue'), props: { type: 'joined', title: 'A cui partecipo' } },
      { path: 'owner', name: 'sharings.owner', component: page('sharings/owner.vue'), props: { type: 'owner' } }
    ]
  },

  { path: '/notifications', name: 'notifications', component: page('notifications.vue') },

  { path: '/settings',
    component: page('settings/index.vue'),
    children: [
      { path: '', redirect: { name: 'settings.profile' } },
      { path: 'profile', name: 'settings.profile', component: page('settings/profile.vue') },
      { path: 'firstinfo', name: 'settings.firstinfo', component: page('settings/firstinfo.vue') },
      { path: 'neededinfo', name: 'settings.neededinfo', component: page('settings/neededinfo.vue') },
      { path: 'verifyaccount', name: 'settings.verifyaccount', component: page('settings/verifyaccount.vue') },
      { path: 'bankaccount', name: 'settings.bankaccount', component: page('settings/bankaccount.vue') },
      { path: 'password', name: 'settings.password', component: page('settings/password.vue') },
      { path: 'wallet', name: 'settings.wallet', component: page('settings/wallet.vue') },
      { path: 'paymentmethods', name: 'settings.paymentmethods', component: page('settings/paymentmethods.vue') }
    ] },

  { path: '*', name: '404', component: page('errors/404.vue') }
]
