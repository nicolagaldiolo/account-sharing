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

  { path: '/sharing/create', name: 'sharing.requests', component: page('sharings/create.vue') },

  { path: '/sharings',
    component: page('sharings/index.vue'),
    children: [
      { path: '', name: 'sharings', redirect: { name: 'sharings.pending' } },
      { path: 'pending', name: 'sharings.pending', component: page('sharings/lists.vue'), props: { type: 'pending' } },
      { path: 'approved', name: 'sharings.approved', component: page('sharings/lists.vue'), props: { type: 'approved' } },
      { path: 'joined', name: 'sharings.joined', component: page('sharings/lists.vue'), props: { type: 'joined' } },
      { path: 'owner', name: 'sharings.owner', component: page('sharings/owner.vue'), props: { type: 'owner' } }
    ]
  },

  { path: '/feed',
    component: page('feed/index.vue'),
    children: [
      { path: '', name: 'feed', redirect: { name: 'feed.requests' } },
      { path: 'requests', name: 'feed.requests', component: page('feed/requests.vue') },
      { path: 'notifications', name: 'feed.notifications', component: page('feed/notifications.vue') }
    ]
  },

  { path: '/settings',
    component: page('settings/index.vue'),
    children: [
      { path: '', redirect: { name: 'settings.profile' } },
      { path: 'profile', name: 'settings.profile', component: page('settings/profile.vue') },
      { path: 'password', name: 'settings.password', component: page('settings/password.vue') },
      { path: 'wallet', name: 'settings.wallet', component: page('settings/wallet.vue') }
    ] },

  { path: '*', component: page('errors/404.vue') }
]
