<template>
  <div class="flex-1 flex flex-col md:flex-row h-[calc(100vh-4rem)]">
    <!-- Left Side: Branding / Content -->
    <div class="hidden md:flex md:w-1/2 bg-mushroom/20 border-r border-elephant/10 p-12 flex-col justify-center relative overflow-hidden">
      <div class="relative z-10 max-w-lg mx-auto space-y-6">
        <h1 class="text-4xl lg:text-5xl font-bold text-gunmetal tracking-tight leading-tight">
          Limit Order Exchange <br />
          <span class="text-thatch">Mini-Engine</span>
        </h1>
        <p class="text-lg text-elephant leading-relaxed">
          Experience a high-performance, real-time matching engine. Place limit orders, manage your crypto assets, and execute trades instantly with secure USD funds.
        </p>
        <ul class="space-y-3 pt-6">
          <li class="flex items-center text-gunmetal font-medium">
            <svg class="w-5 h-5 text-thatch mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            Atomic trade execution & matching
          </li>
          <li class="flex items-center text-gunmetal font-medium">
            <svg class="w-5 h-5 text-thatch mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            Real-time orderbook updates via Pusher
          </li>
          <li class="flex items-center text-gunmetal font-medium">
            <svg class="w-5 h-5 text-thatch mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            Secure wallet and balance locking
          </li>
        </ul>
      </div>
      <!-- Decorative element -->
      <div class="absolute -bottom-24 -left-24 w-96 h-96 bg-thatch/10 rounded-full blur-3xl pointer-events-none"></div>
      <div class="absolute -top-24 -right-24 w-80 h-80 bg-gunmetal/5 rounded-full blur-3xl pointer-events-none"></div>
    </div>

    <!-- Right Side: Login Form -->
    <div class="w-full md:w-1/2 flex items-center justify-center p-6 sm:p-12">
      <div class="w-full max-w-md space-y-8">
        <div class="text-center md:text-left">
          <h2 class="text-3xl font-bold text-gunmetal">{{ isRegistering ? 'Create Account' : 'Welcome back' }}</h2>
          <p class="text-elephant mt-2">{{ isRegistering ? 'Sign up to start trading.' : 'Sign in to access your trading dashboard.' }}</p>
        </div>

        <form @submit.prevent="isRegistering ? handleRegister() : handleLogin()" class="space-y-5 bg-white/60 p-8 rounded-2xl border border-elephant/10 shadow-sm">
          <div v-if="isRegistering">
            <label class="block text-sm font-medium text-elephant mb-1.5">Full Name</label>
            <input v-model="name" type="text" placeholder="Enter your full name" class="w-full bg-white border border-elephant/30 rounded-xl px-4 py-3 text-gunmetal focus:outline-none focus:ring-2 focus:ring-thatch focus:border-transparent transition-all placeholder:text-elephant/50" required>
          </div>
          <div>
            <label class="block text-sm font-medium text-elephant mb-1.5">Email address</label>
            <input v-model="email" type="email" placeholder="Enter your email" class="w-full bg-white border border-elephant/30 rounded-xl px-4 py-3 text-gunmetal focus:outline-none focus:ring-2 focus:ring-thatch focus:border-transparent transition-all placeholder:text-elephant/50" required>
          </div>
          <div>
            <label class="block text-sm font-medium text-elephant mb-1.5">Password</label>
            <div class="relative">
              <input v-model="password" :type="showPassword ? 'text' : 'password'" placeholder="Enter your password" class="w-full bg-white border border-elephant/30 rounded-xl px-4 py-3 text-gunmetal focus:outline-none focus:ring-2 focus:ring-thatch focus:border-transparent transition-all placeholder:text-elephant/50" required>
              <button type="button" @click="showPassword = !showPassword" class="absolute right-3 top-1/2 -translate-y-1/2 text-elephant hover:text-gunmetal transition-colors">
                <svg v-if="!showPassword" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <svg v-else xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                </svg>
              </button>
            </div>
          </div>
          <div v-if="isRegistering">
            <label class="block text-sm font-medium text-elephant mb-1.5">Confirm Password</label>
            <div class="relative">
              <input v-model="password_confirmation" :type="showConfirmPassword ? 'text' : 'password'" placeholder="Confirm your password" class="w-full bg-white border border-elephant/30 rounded-xl px-4 py-3 text-gunmetal focus:outline-none focus:ring-2 focus:ring-thatch focus:border-transparent transition-all placeholder:text-elephant/50" required>
              <button type="button" @click="showConfirmPassword = !showConfirmPassword" class="absolute right-3 top-1/2 -translate-y-1/2 text-elephant hover:text-gunmetal transition-colors">
                <svg v-if="!showConfirmPassword" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <svg v-else xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                </svg>
              </button>
            </div>
          </div>
          <div v-if="error" class="text-thatch text-sm bg-thatch/10 p-3 rounded-lg border border-thatch/20">
            {{ error }}
          </div>
          <button :disabled="loading" class="w-full bg-gunmetal hover:bg-gunmetal/90 text-white font-medium py-3 rounded-xl transition-all cursor-pointer disabled:opacity-50 shadow-md shadow-gunmetal/20 mt-4">
            {{ loading ? 'Processing...' : (isRegistering ? 'Register' : 'Login') }}
          </button>
          
          <div class="text-center mt-6">
            <button type="button" @click="isRegistering = !isRegistering" class="text-sm font-medium text-elephant hover:text-gunmetal transition-colors">
              {{ isRegistering ? 'Already have an account? Login' : "Don't have an account? Register" }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useAuth } from '../composables/useAuth';

const {
  name,
  email,
  password,
  password_confirmation,
  showPassword,
  showConfirmPassword,
  isRegistering,
  error,
  loading,
  handleLogin,
  handleRegister
} = useAuth();
</script>
