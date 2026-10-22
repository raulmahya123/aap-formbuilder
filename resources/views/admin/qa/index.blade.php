@extends('layouts.app')
@section('title', 'Mandala Chat')

@push('styles')
<style>
  /* Mandala Chat telegram-inspired styles */
  :root {
    --chat-primary: #1565D8;
    --chat-primary-dark: #0B4DB3;
    --chat-bg: #F8FAFC;
    --chat-border: #E2E8F0;
    --chat-bubble-in: #F1F5F9;
    --chat-bubble-out: #1565D8;
    --chat-text-main: #111827;
    --chat-text-muted: #475569;
  }

  .mandala-chat-container {
    height: calc(100vh - 4.5rem);
    min-height: 580px;
  }

  .chat-scroll {
    scrollbar-width: thin;
    scrollbar-color: #CBD5E1 transparent;
  }
  .chat-scroll::-webkit-scrollbar {
    width: 5px;
    height: 5px;
  }
  .chat-scroll::-webkit-scrollbar-thumb {
    background: #CBD5E1;
    border-radius: 4px;
  }

  /* Skeleton animation */
  @keyframes chatPulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.4; }
  }
  .chat-pulse {
    animation: chatPulse 1.5s cubic-bezier(0.4, 0, 0.6, 1) infinite;
  }
</style>
@endpush

@section('content')
<div class="sr-only">
  <x-ui.page-header>
    <x-ui.page-title>Mandala Chat</x-ui.page-title>
    <x-ui.page-description>Sistem Komunikasi Internal Mandala</x-ui.page-description>
  </x-ui.page-header>
</div>
<div x-data="mandalaChat({
    activeThreadId: {{ $activeThreadId ? (int)$activeThreadId : 'null' }},
    currentUserId: {{ auth()->id() }},
    currentUserName: '{{ addslashes(auth()->user()->name) }}',
    currentUserRole: '{{ auth()->user()->role }}',
    selectedSiteId: '{{ $selectedSiteId }}',
    routes: {
      conversations: '{{ route('admin.qa.conversations') }}',
      searchEmployees: '{{ route('admin.qa.employees.search') }}',
      startPersonal: '{{ route('admin.qa.personal.start') }}',
      createGroup: '{{ route('admin.qa.groups.create') }}',
    }
  })" 
  x-init="initChat()" 
  class="mandala-chat-container -mx-4 -my-6 lg:-mx-8 flex overflow-hidden bg-[#F8FAFC] border-t border-[#E2E8F0]"
  @keydown.escape.window="closeAllModals()"
>

  <!-- ======================================================== -->
  <!-- B. CONVERSATION SIDEBAR (320px - 360px)                 -->
  <!-- ======================================================== -->
  <aside 
    class="w-full md:w-[340px] lg:w-[360px] shrink-0 border-r border-[#E2E8F0] bg-white flex flex-col h-full z-10"
    :class="{'hidden md:flex': activeThread !== null, 'flex': activeThread === null}"
  >
    <!-- Sidebar Header -->
    <div class="px-4 py-3.5 border-b border-[#E2E8F0] flex items-center justify-between gap-2 bg-white">
      <div class="flex items-center gap-2.5">
        <div class="w-8 h-8 rounded-lg bg-[#1565D8] text-white flex items-center justify-center font-bold text-sm shadow-sm">
          <x-ui-icon name="message-circle" class="w-4 h-4 text-white" />
        </div>
        <div>
          <h1 class="text-base font-bold text-[#111827] leading-tight">Mandala Chat</h1>
          <p class="text-[11px] text-[#475569] leading-none mt-0.5">Komunikasi Internal</p>
        </div>
      </div>

      <!-- Action Buttons Dropdown -->
      <div class="flex items-center gap-1" x-data="{ openMenu: false }">
        <button 
          type="button" 
          @click="openNewChatModal()"
          class="p-2 text-[#475569] hover:text-[#1565D8] hover:bg-[#F1F5F9] rounded-lg transition"
          title="Mulai Chat Pribadi"
          aria-label="New Chat"
        >
          <x-ui-icon name="plus" class="w-5 h-5" />
        </button>

        <div class="relative">
          <button 
            type="button" 
            @click="openMenu = !openMenu" 
            @click.outside="openMenu = false"
            class="p-2 text-[#475569] hover:text-[#111827] hover:bg-[#F1F5F9] rounded-lg transition"
            title="Menu lainnya"
            aria-label="More Options"
          >
            <x-ui-icon name="more-vertical" class="w-5 h-5" />
          </button>

          <div 
            x-show="openMenu" 
            x-transition 
            x-cloak
            class="absolute right-0 mt-1 w-48 bg-white border border-[#E2E8F0] rounded-xl shadow-lg py-1.5 z-50 text-sm"
          >
            <button 
              type="button" 
              @click="openMenu = false; openNewGroupModal()" 
              class="w-full text-left px-3.5 py-2 text-[#111827] hover:bg-[#F8FAFC] flex items-center gap-2.5"
            >
              <x-ui-icon name="groups" class="w-4 h-4 text-[#1565D8]" />
              <span>Buat Grup Baru</span>
            </button>
            <button 
              type="button" 
              @click="openMenu = false; fetchConversations()" 
              class="w-full text-left px-3.5 py-2 text-[#111827] hover:bg-[#F8FAFC] flex items-center gap-2.5"
            >
              <x-ui-icon name="check" class="w-4 h-4 text-[#475569]" />
              <span>Segarkan Percakapan</span>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Search Input -->
    <div class="p-3 border-b border-[#E2E8F0] bg-white">
      <div class="relative">
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-[#94A3B8]">
          <x-ui-icon name="search" class="w-4 h-4" />
        </div>
        <input 
          type="text" 
          x-model="searchQuery" 
          @input.debounce.350ms="fetchConversations()"
          placeholder="Cari pegawai, grup, site..."
          class="w-full pl-9 pr-8 py-2 text-xs bg-[#F8FAFC] border border-[#E2E8F0] rounded-lg text-[#111827] placeholder-[#94A3B8] focus:bg-white focus:border-[#1565D8] focus:ring-1 focus:ring-[#1565D8] transition"
        />
        <button 
          x-show="searchQuery" 
          @click="searchQuery = ''; fetchConversations()" 
          class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-[#94A3B8] hover:text-[#475569]"
        >
          <x-ui-icon name="x" class="w-3.5 h-3.5" />
        </button>
      </div>

      <!-- Filter Tabs -->
      <div class="flex items-center gap-1 mt-2.5 bg-[#F1F5F9] p-0.5 rounded-lg text-xs font-medium">
        <button 
          type="button"
          @click="activeTab = 'all'; fetchConversations()"
          class="flex-1 py-1.5 rounded-md transition text-center"
          :class="activeTab === 'all' ? 'bg-white text-[#1565D8] font-semibold shadow-xs' : 'text-[#475569] hover:text-[#111827]'"
        >
          Semua
        </button>
        <button 
          type="button"
          @click="activeTab = 'personal'; fetchConversations()"
          class="flex-1 py-1.5 rounded-md transition text-center"
          :class="activeTab === 'personal' ? 'bg-white text-[#1565D8] font-semibold shadow-xs' : 'text-[#475569] hover:text-[#111827]'"
        >
          Personal
        </button>
        <button 
          type="button"
          @click="activeTab = 'group'; fetchConversations()"
          class="flex-1 py-1.5 rounded-md transition text-center"
          :class="activeTab === 'group' ? 'bg-white text-[#1565D8] font-semibold shadow-xs' : 'text-[#475569] hover:text-[#111827]'"
        >
          Grup
        </button>
        <button 
          type="button"
          @click="activeTab = 'unread'; fetchConversations()"
          class="flex-1 py-1.5 rounded-md transition text-center"
          :class="activeTab === 'unread' ? 'bg-white text-[#1565D8] font-semibold shadow-xs' : 'text-[#475569] hover:text-[#111827]'"
        >
          Unread
        </button>
      </div>
    </div>

    <!-- Conversation List -->
    <div class="flex-1 overflow-y-auto chat-scroll divide-y divide-[#F1F5F9]">
      <!-- Loading Skeleton -->
      <template x-if="loadingConversations">
        <div class="p-3 space-y-3">
          <template x-for="i in 5" :key="i">
            <div class="flex items-center gap-3 p-2 rounded-lg chat-pulse">
              <div class="w-11 h-11 rounded-full bg-[#E2E8F0] shrink-0"></div>
              <div class="flex-1 space-y-1.5">
                <div class="h-3.5 bg-[#E2E8F0] rounded w-3/4"></div>
                <div class="h-2.5 bg-[#E2E8F0] rounded w-1/2"></div>
              </div>
            </div>
          </template>
        </div>
      </template>

      <!-- Empty State -->
      <template x-if="!loadingConversations && conversations.length === 0">
        <div class="p-8 text-center text-[#475569] flex flex-col items-center justify-center h-48">
          <div class="w-12 h-12 rounded-full bg-[#F1F5F9] flex items-center justify-center text-[#94A3B8] mb-3">
            <x-ui-icon name="message-circle" class="w-6 h-6" />
          </div>
          <p class="text-sm font-medium text-[#111827]">Belum ada obrolan</p>
          <p class="text-xs text-[#94A3B8] mt-1">Mulai obrolan baru dengan rekan kerja Anda.</p>
          <button 
            type="button" 
            @click="openNewChatModal()"
            class="mt-3 px-3 py-1.5 text-xs font-semibold text-white bg-[#1565D8] rounded-lg hover:bg-[#0B4DB3] transition"
          >
            Chat Baru
          </button>
        </div>
      </template>

      <!-- Conversation Item -->
      <template x-for="conv in conversations" :key="conv.id">
        <div 
          @click="selectConversation(conv)"
          @contextmenu.prevent="openContextMenu($event, conv)"
          class="flex items-center gap-3 px-3.5 py-3 cursor-pointer transition select-none group relative"
          :class="activeThread && activeThread.id === conv.id ? 'bg-[#EFF6FF] border-l-4 border-[#1565D8]' : 'hover:bg-[#F8FAFC] border-l-4 border-transparent'"
        >
          <!-- Avatar with Online / Type badge -->
          <div class="relative shrink-0">
            <template x-if="conv.avatar_url">
              <img :src="conv.avatar_url" class="w-11 h-11 rounded-full object-cover border border-[#E2E8F0]" alt="" />
            </template>
            <template x-if="!conv.avatar_url">
              <div 
                class="w-11 h-11 rounded-full flex items-center justify-center font-bold text-xs"
                :class="conv.type === 'personal' ? 'bg-[#E0E7FF] text-[#3730A3]' : (conv.type === 'site' ? 'bg-[#DCFCE7] text-[#166534]' : 'bg-[#FEF3C7] text-[#92400E]')"
                x-text="conv.initials"
              ></div>
            </template>

            <!-- Site badge if applicable -->
            <template x-if="conv.site">
              <span 
                class="absolute -bottom-1 -right-1 text-[9px] font-bold px-1 py-0.2 rounded bg-[#111827] text-white border border-white"
                x-text="conv.site.code"
              ></span>
            </template>
          </div>

          <!-- Body Info -->
          <div class="flex-1 min-w-0">
            <div class="flex items-center justify-between gap-1 mb-0.5">
              <div class="flex items-center gap-1.5 min-w-0">
                <span class="text-xs font-semibold text-[#111827] truncate" x-text="conv.subject"></span>
                <template x-if="conv.is_pinned">
                  <x-ui-icon name="pin" class="w-3 h-3 text-[#1565D8] shrink-0" />
                </template>
                <template x-if="conv.is_muted">
                  <x-ui-icon name="bell-off" class="w-3 h-3 text-[#94A3B8] shrink-0" />
                </template>
              </div>
              <span 
                class="text-[10px] text-[#94A3B8] shrink-0" 
                x-text="conv.last_message ? conv.last_message.created_at : ''"
              ></span>
            </div>

            <div class="flex items-center justify-between gap-2">
              <p class="text-xs text-[#475569] truncate">
                <template x-if="conv.last_message">
                  <span>
                    <strong x-show="conv.type !== 'personal' && conv.last_message.user_id !== currentUserId" x-text="conv.last_message.user_name + ': '" class="font-medium text-[#111827]"></strong>
                    <span x-text="conv.last_message.body"></span>
                  </span>
                </template>
                <template x-if="!conv.last_message">
                  <span class="text-[#94A3B8] italic">Belum ada pesan</span>
                </template>
              </p>

              <!-- Unread Badge -->
              <template x-if="conv.unread_count > 0">
                <span 
                  class="shrink-0 bg-[#1565D8] text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full min-w-[18px] text-center"
                  x-text="conv.unread_count"
                ></span>
              </template>
            </div>
          </div>
        </div>
      </template>
    </div>
  </aside>

  <!-- ======================================================== -->
  <!-- C. MAIN CONVERSATION PANEL                               -->
  <!-- ======================================================== -->
  <main 
    class="flex-1 flex flex-col h-full bg-[#F8FAFC] relative overflow-hidden"
    :class="{'hidden md:flex': activeThread === null, 'flex': activeThread !== null}"
  >
    <!-- Placeholder when no thread is selected -->
    <template x-if="!activeThread">
      <div class="flex-1 flex flex-col items-center justify-center p-6 text-center">
        <div class="w-16 h-16 rounded-2xl bg-white border border-[#E2E8F0] shadow-sm flex items-center justify-center text-[#1565D8] mb-4">
          <x-ui-icon name="message-circle" class="w-8 h-8" />
        </div>
        <h2 class="text-lg font-bold text-[#111827]">Pilih Percakapan</h2>
        <p class="text-xs text-[#475569] max-w-sm mt-1">
          Pilih salah satu percakapan di bilah kiri atau mulai percakapan baru untuk mengirim pesan, berbagi dokumen, atau membuat laporan pekerjaan.
        </p>
        <button 
          type="button"
          @click="openNewChatModal()"
          class="mt-4 px-4 py-2 text-xs font-semibold text-white bg-[#1565D8] rounded-lg hover:bg-[#0B4DB3] transition shadow-sm inline-flex items-center gap-2"
        >
          <x-ui-icon name="plus" class="w-4 h-4" />
          <span>Mulai Chat Baru</span>
        </button>
      </div>
    </template>

    <!-- Active Thread Container -->
    <template x-if="activeThread">
      <div class="flex-1 flex flex-col h-full overflow-hidden">
        
        <!-- Header -->
        <header class="px-4 py-3 bg-white border-b border-[#E2E8F0] flex items-center justify-between gap-3 shrink-0 z-10 shadow-xs">
          <div class="flex items-center gap-3 min-w-0">
            <!-- Back button for mobile -->
            <button 
              type="button" 
              @click="activeThread = null"
              class="md:hidden p-1.5 -ml-1 text-[#475569] hover:text-[#111827] rounded-lg"
              title="Kembali ke daftar"
            >
              <x-ui-icon name="arrow-left" class="w-5 h-5" />
            </button>

            <!-- Conversation Avatar -->
            <div class="w-10 h-10 rounded-full shrink-0 flex items-center justify-center font-bold text-xs"
              :class="activeThread.type === 'personal' ? 'bg-[#E0E7FF] text-[#3730A3]' : (activeThread.type === 'site' ? 'bg-[#DCFCE7] text-[#166534]' : 'bg-[#FEF3C7] text-[#92400E]')"
            >
              <template x-if="activeThread.avatar_url">
                <img :src="activeThread.avatar_url" class="w-full h-full rounded-full object-cover" alt="" />
              </template>
              <template x-if="!activeThread.avatar_url">
                <span x-text="activeThread.initials"></span>
              </template>
            </div>

            <!-- Subject & Status -->
            <div class="min-w-0">
              <h2 class="text-sm font-bold text-[#111827] truncate flex items-center gap-1.5">
                <span x-text="activeThread.subject"></span>
                <template x-if="activeThread.site">
                  <span class="text-[10px] px-1.5 py-0.5 rounded font-medium bg-[#F1F5F9] text-[#475569]" x-text="activeThread.site.code"></span>
                </template>
              </h2>
              <p class="text-[11px] text-[#475569] truncate">
                <template x-if="activeThread.type === 'personal'">
                  <span>Personal Chat</span>
                </template>
                <template x-if="activeThread.type !== 'personal'">
                  <span x-text="activeThread.member_count + ' anggota' + (activeThread.description ? ' · ' + activeThread.description : '')"></span>
                </template>
              </p>
            </div>
          </div>

          <!-- Actions -->
          <div class="flex items-center gap-1.5">
            <!-- Create Report Button -->
            <button 
              type="button"
              @click="openReportModal()"
              class="px-2.5 py-1.5 text-xs font-semibold text-[#1565D8] bg-[#EFF6FF] border border-[#BFDBFE] hover:bg-[#DBEAFE] rounded-lg transition inline-flex items-center gap-1.5"
              title="Buat Laporan Pekerjaan"
            >
              <x-ui-icon name="report" class="w-4 h-4 text-[#1565D8]" />
              <span class="hidden sm:inline">Buat Laporan</span>
            </button>

            <!-- Toggle Info Panel -->
            <button 
              type="button" 
              @click="infoPanelOpen = !infoPanelOpen"
              class="p-2 text-[#475569] hover:text-[#111827] hover:bg-[#F1F5F9] rounded-lg transition"
              :class="{'text-[#1565D8] bg-[#EFF6FF]': infoPanelOpen}"
              title="Informasi Percakapan"
              aria-label="Info"
            >
              <x-ui-icon name="info" class="w-5 h-5" />
            </button>
          </div>
        </header>

        <!-- Pinned Messages Banner -->
        <template x-if="activeThread.pinned_messages && activeThread.pinned_messages.length > 0">
          <div class="bg-[#F8FAFC] border-b border-[#E2E8F0] px-4 py-2 flex items-center justify-between text-xs text-[#111827] shrink-0">
            <div class="flex items-center gap-2 min-w-0">
              <x-ui-icon name="pin" class="w-3.5 h-3.5 text-[#1565D8] shrink-0" />
              <span class="font-semibold shrink-0">Pesan Tersemat:</span>
              <span class="truncate text-[#475569]" x-text="activeThread.pinned_messages[0].user_name + ': ' + activeThread.pinned_messages[0].body"></span>
            </div>
          </div>
        </template>

        <!-- Message Body -->
        <div 
          x-ref="messageList"
          @scroll="handleScroll()"
          class="flex-1 overflow-y-auto chat-scroll p-4 space-y-3 bg-[#F8FAFC]"
        >
          <!-- Load older messages indicator -->
          <template x-if="loadingOlderMessages">
            <div class="text-center py-2">
              <span class="text-xs text-[#94A3B8] font-medium">Memuat pesan terdahulu...</span>
            </div>
          </template>

          <template x-if="messages.length === 0 && !loadingMessages">
            <div class="text-center py-12 text-[#94A3B8]">
              <p class="text-xs">Belum ada pesan dalam percakapan ini.</p>
              <p class="text-[11px] mt-0.5">Jadilah yang pertama mengirimkan pesan!</p>
            </div>
          </template>

          <!-- Group messages by date -->
          <template x-for="(msg, index) in messages" :key="msg.id">
            <div class="space-y-3">
              <!-- Date Separator -->
              <template x-if="index === 0 || msg.created_date !== messages[index - 1].created_date">
                <div class="flex items-center justify-center my-3">
                  <span class="bg-[#E2E8F0] text-[#475569] text-[10px] font-bold px-3 py-1 rounded-full uppercase tracking-wider" x-text="msg.created_date_label"></span>
                </div>
              </template>

              <!-- System Message -->
              <template x-if="msg.message_type === 'system'">
                <div class="flex items-center justify-center my-2">
                  <span class="bg-[#F1F5F9] border border-[#E2E8F0] text-[#475569] text-[11px] px-3 py-1 rounded-full text-center" x-text="msg.body"></span>
                </div>
              </template>

              <!-- Standard / Report Message -->
              <template x-if="msg.message_type !== 'system'">
                <div 
                  class="flex items-end gap-2 group"
                  :class="msg.is_mine ? 'justify-end' : 'justify-start'"
                >
                  <!-- Avatar for incoming -->
                  <template x-if="!msg.is_mine">
                    <div class="w-7 h-7 rounded-full bg-[#E2E8F0] text-[#475569] flex items-center justify-center font-bold text-[10px] shrink-0 mb-1">
                      <template x-if="msg.user_avatar">
                        <img :src="msg.user_avatar" class="w-full h-full rounded-full object-cover" alt="" />
                      </template>
                      <template x-if="!msg.user_avatar">
                        <span x-text="msg.user_initials"></span>
                      </template>
                    </div>
                  </template>

                  <!-- Message Bubble Container -->
                  <div class="max-w-[85%] sm:max-w-[70%] relative group">
                    
                    <!-- Bubble Content -->
                    <div 
                      class="rounded-2xl px-3.5 py-2.5 text-xs shadow-xs relative"
                      :class="msg.is_mine ? 'bg-[#1565D8] text-white rounded-br-xs' : 'bg-[#F1F5F9] text-[#111827] border border-[#E2E8F0] rounded-bl-xs'"
                    >
                      <!-- Sender name in group -->
                      <template x-if="!msg.is_mine && activeThread.type !== 'personal'">
                        <div class="font-bold text-[11px] text-[#0B4DB3] mb-1 flex items-center gap-1.5">
                          <span x-text="msg.user_name"></span>
                          <template x-if="msg.user_role === 'super_admin' || msg.user_role === 'admin'">
                            <span class="text-[9px] px-1 rounded bg-[#DBEAFE] text-[#1E40AF]">Admin</span>
                          </template>
                        </div>
                      </template>

                      <!-- Reply Context Preview -->
                      <template x-if="msg.parent">
                        <div 
                          class="mb-2 p-2 rounded-lg text-[11px] border-l-2 cursor-pointer"
                          :class="msg.is_mine ? 'bg-white/10 border-white text-white/90' : 'bg-white border-[#1565D8] text-[#475569]'"
                        >
                          <p class="font-bold text-[10px]" x-text="msg.parent.user_name"></p>
                          <p class="truncate" x-text="msg.parent.body"></p>
                        </div>
                      </template>

                      <!-- STRUCTURED REPORT CARD (Phase 4.4) -->
                      <template x-if="msg.report">
                        <div class="p-3 bg-white text-[#111827] rounded-xl border border-[#CBD5E1] my-1 shadow-xs space-y-2">
                          <div class="flex items-start justify-between gap-2 border-b border-[#E2E8F0] pb-2">
                            <div>
                              <span class="text-[10px] font-bold uppercase tracking-wider text-[#1565D8]" x-text="msg.report.report_type"></span>
                              <h4 class="font-bold text-sm text-[#111827]" x-text="msg.report.title"></h4>
                            </div>
                            <span 
                              class="text-[10px] font-bold px-2 py-0.5 rounded-full uppercase"
                              :class="{
                                'bg-[#FEF3C7] text-[#92400E]': msg.report.status === 'open',
                                'bg-[#DBEAFE] text-[#1E40AF]': msg.report.status === 'in_progress',
                                'bg-[#DCFCE7] text-[#166534]': msg.report.status === 'resolved',
                                'bg-[#F1F5F9] text-[#475569]': msg.report.status === 'closed'
                              }"
                              x-text="msg.report.status.replace('_', ' ')"
                            ></span>
                          </div>

                          <p class="text-xs text-[#334155] whitespace-pre-line" x-text="msg.report.description"></p>

                          <div class="grid grid-cols-2 gap-2 text-[10px] text-[#64748B] pt-1 border-t border-[#F1F5F9]">
                            <div><strong>Site:</strong> <span x-text="msg.report.site_code || '-'"></span></div>
                            <div><strong>Prioritas:</strong> <span class="capitalize" x-text="msg.report.priority"></span></div>
                            <div><strong>Dept:</strong> <span x-text="msg.report.department_name || '-'"></span></div>
                            <div><strong>Assignee:</strong> <span x-text="msg.report.assignee_name || '-'"></span></div>
                          </div>

                          <!-- Action to update status -->
                          <div class="pt-2 flex items-center justify-between border-t border-[#F1F5F9]">
                            <button 
                              type="button" 
                              @click="openReportDetailModal(msg.report)"
                              class="text-[11px] font-semibold text-[#1565D8] hover:underline"
                            >
                              Lihat Riwayat & Ubah Status →
                            </button>
                          </div>
                        </div>
                      </template>

                      <!-- Message Text -->
                      <template x-if="!msg.report && msg.body">
                        <div class="whitespace-pre-wrap leading-relaxed select-text" x-text="msg.body"></div>
                      </template>

                      <!-- Attachments Preview -->
                      <template x-if="msg.attachments && msg.attachments.length > 0">
                        <div class="mt-2 space-y-1.5">
                          <template x-for="att in msg.attachments" :key="att.index">
                            <div>
                              <!-- Image Preview -->
                              <template x-if="att.is_image">
                                <a :href="att.url" target="_blank" class="block rounded-lg overflow-hidden border border-black/10 max-w-xs hover:opacity-95 transition">
                                  <img :src="att.url" class="max-h-56 w-auto object-cover" alt="" />
                                </a>
                              </template>

                              <!-- File download button -->
                              <template x-if="!att.is_image">
                                <a 
                                  :href="att.url" 
                                  class="flex items-center gap-2 p-2 rounded-lg border text-[11px] font-medium transition"
                                  :class="msg.is_mine ? 'bg-white/15 border-white/20 text-white hover:bg-white/25' : 'bg-white border-[#E2E8F0] text-[#111827] hover:bg-[#F8FAFC]'"
                                >
                                  <x-ui-icon name="file" class="w-4 h-4 shrink-0" />
                                  <span class="truncate flex-1" x-text="att.name"></span>
                                  <span class="text-[9px] opacity-75 shrink-0" x-text="att.size"></span>
                                </a>
                              </template>
                            </div>
                          </template>
                        </div>
                      </template>

                      <!-- Meta timestamp & status -->
                      <div 
                        class="flex items-center justify-end gap-1 mt-1 text-[9px]"
                        :class="msg.is_mine ? 'text-white/75' : 'text-[#64748B]'"
                      >
                        <span x-show="msg.is_edited" class="italic mr-0.5">diedit</span>
                        <span x-text="msg.created_at"></span>
                        <template x-if="msg.is_mine">
                          <x-ui-icon name="check-check" class="w-3.5 h-3.5" />
                        </template>
                      </div>
                    </div>

                    <!-- Reactions Display -->
                    <template x-if="msg.reactions && msg.reactions.length > 0">
                      <div class="flex items-center gap-1 mt-1">
                        <template x-for="rx in msg.reactions" :key="rx.emoji">
                          <button 
                            type="button"
                            @click="toggleReaction(msg, rx.emoji)"
                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] border transition"
                            :class="rx.has_mine ? 'bg-[#EFF6FF] border-[#1565D8] text-[#1565D8] font-bold' : 'bg-white border-[#E2E8F0] text-[#475569]'"
                          >
                            <span x-text="rx.emoji"></span>
                            <span x-text="rx.count"></span>
                          </button>
                        </template>
                      </div>
                    </template>

                    <!-- Hover Quick Actions Toolbar -->
                    <div 
                      class="absolute -top-3 right-2 hidden group-hover:flex items-center gap-0.5 bg-white border border-[#E2E8F0] rounded-lg shadow-sm px-1 py-0.5 z-20"
                    >
                      <!-- Quick reaction -->
                      <button type="button" @click="toggleReaction(msg, '👍')" class="p-1 hover:bg-[#F1F5F9] rounded text-xs" title="Suka">👍</button>
                      <button type="button" @click="toggleReaction(msg, '❤️')" class="p-1 hover:bg-[#F1F5F9] rounded text-xs" title="Love">❤️</button>
                      
                      <!-- Reply -->
                      <button type="button" @click="setReplyTo(msg)" class="p-1 text-[#475569] hover:text-[#111827] hover:bg-[#F1F5F9] rounded" title="Balas">
                        <x-ui-icon name="reply" class="w-3.5 h-3.5" />
                      </button>

                      <!-- Copy -->
                      <button type="button" @click="copyMessage(msg)" class="p-1 text-[#475569] hover:text-[#111827] hover:bg-[#F1F5F9] rounded" title="Salin">
                        <x-ui-icon name="copy" class="w-3.5 h-3.5" />
                      </button>

                      <!-- Pin -->
                      <button type="button" @click="togglePinMessage(msg)" class="p-1 text-[#475569] hover:text-[#111827] hover:bg-[#F1F5F9] rounded" title="Pin Pesan">
                        <x-ui-icon name="pin" class="w-3.5 h-3.5" />
                      </button>

                      <!-- Edit if mine -->
                      <template x-if="msg.can_edit && !msg.report">
                        <button type="button" @click="openEditMessageModal(msg)" class="p-1 text-[#475569] hover:text-[#111827] hover:bg-[#F1F5F9] rounded" title="Edit">
                          <x-ui-icon name="edit" class="w-3.5 h-3.5" />
                        </button>
                      </template>

                      <!-- Delete if allowed -->
                      <template x-if="msg.can_delete">
                        <button type="button" @click="openDeleteMessageModal(msg)" class="p-1 text-[#DC2626] hover:bg-[#FEF2F2] rounded" title="Hapus">
                          <x-ui-icon name="trash" class="w-3.5 h-3.5" />
                        </button>
                      </template>
                    </div>

                  </div>
                </div>
              </template>
            </div>
          </template>
        </div>

        <!-- Sticky Jump to Latest Button -->
        <button 
          type="button" 
          x-show="showScrollDown"
          @click="scrollToBottom(true)"
          class="absolute bottom-24 right-8 p-2.5 bg-white border border-[#E2E8F0] shadow-md rounded-full text-[#1565D8] hover:bg-[#F8FAFC] transition z-30"
          title="Ke pesan terbaru"
        >
          <x-ui-icon name="arrow-down" class="w-4 h-4" />
        </button>

        <!-- Composer (Sticky Bottom) -->
        <footer class="p-3 bg-white border-t border-[#E2E8F0] shrink-0 z-20">
          
          <!-- Reply Preview Bar -->
          <template x-if="replyingTo">
            <div class="flex items-center justify-between bg-[#EFF6FF] border border-[#BFDBFE] px-3 py-1.5 rounded-lg mb-2 text-xs">
              <div class="flex items-center gap-2 min-w-0">
                <x-ui-icon name="reply" class="w-3.5 h-3.5 text-[#1565D8] shrink-0" />
                <div class="min-w-0">
                  <span class="font-bold text-[#1565D8]" x-text="'Membalas ' + replyingTo.user_name"></span>
                  <p class="truncate text-[#475569]" x-text="replyingTo.body"></p>
                </div>
              </div>
              <button type="button" @click="replyingTo = null" class="text-[#94A3B8] hover:text-[#475569]">
                <x-ui-icon name="x" class="w-4 h-4" />
              </button>
            </div>
          </template>

          <!-- Attachments pending preview -->
          <template x-if="pendingAttachments.length > 0">
            <div class="flex flex-wrap gap-2 mb-2 p-2 bg-[#F8FAFC] border border-[#E2E8F0] rounded-lg">
              <template x-for="(file, idx) in pendingAttachments" :key="idx">
                <div class="flex items-center gap-1.5 px-2 py-1 bg-white border border-[#E2E8F0] rounded text-xs text-[#111827]">
                  <x-ui-icon name="file" class="w-3.5 h-3.5 text-[#1565D8]" />
                  <span class="truncate max-w-[120px]" x-text="file.name"></span>
                  <button type="button" @click="removePendingAttachment(idx)" class="text-[#DC2626] hover:opacity-80">
                    <x-ui-icon name="x" class="w-3 h-3" />
                  </button>
                </div>
              </template>
            </div>
          </template>

          <!-- Input Row -->
          <div class="flex items-end gap-2">
            <!-- Attachment Button -->
            <label 
              class="p-2.5 text-[#475569] hover:text-[#1565D8] hover:bg-[#F1F5F9] rounded-lg cursor-pointer transition shrink-0"
              title="Lampirkan Dokumen / Gambar"
            >
              <x-ui-icon name="paperclip" class="w-5 h-5" />
              <input 
                type="file" 
                multiple 
                @change="handleFileSelect($event)" 
                class="sr-only" 
                accept=".pdf,.doc,.docx,.xls,.xlsx,.png,.jpg,.jpeg,.txt,.webp"
              />
            </label>

            <!-- Textarea -->
            <div class="flex-1 min-w-0 relative">
              <textarea 
                x-ref="messageInput"
                x-model="messageBody" 
                @keydown.enter="handleEnterKey($event)"
                rows="1"
                placeholder="Ketik pesan..."
                class="w-full resize-none py-2.5 px-3.5 text-xs bg-[#F8FAFC] border border-[#E2E8F0] rounded-xl text-[#111827] placeholder-[#94A3B8] focus:bg-white focus:border-[#1565D8] focus:ring-1 focus:ring-[#1565D8] transition max-h-32"
              ></textarea>
            </div>

            <!-- Send Button -->
            <button 
              type="button" 
              @click="sendMessage()"
              :disabled="sendingMessage || (messageBody.trim() === '' && pendingAttachments.length === 0)"
              class="p-2.5 text-white bg-[#1565D8] hover:bg-[#0B4DB3] disabled:opacity-50 disabled:pointer-events-none rounded-xl transition shadow-xs shrink-0"
              title="Kirim (Enter)"
            >
              <x-ui-icon name="send" class="w-5 h-5" />
            </button>
          </div>
        </footer>

      </div>
    </template>
  </main>

  <!-- ======================================================== -->
  <!-- D. INFORMATION SIDEBAR (300px)                           -->
  <!-- ======================================================== -->
  <aside 
    x-show="infoPanelOpen && activeThread"
    x-transition
    x-cloak
    class="w-full sm:w-[300px] shrink-0 border-l border-[#E2E8F0] bg-white flex flex-col h-full z-20 shadow-lg sm:shadow-none"
  >
    <!-- Header -->
    <div class="px-4 py-3.5 border-b border-[#E2E8F0] flex items-center justify-between">
      <h3 class="text-sm font-bold text-[#111827]">Info Percakapan</h3>
      <button type="button" @click="infoPanelOpen = false" class="p-1 text-[#475569] hover:text-[#111827] rounded-lg">
        <x-ui-icon name="x" class="w-4 h-4" />
      </button>
    </div>

    <!-- Body -->
    <div class="flex-1 overflow-y-auto chat-scroll p-4 space-y-5 text-xs">
      <!-- Profile Header -->
      <div class="text-center pb-4 border-b border-[#E2E8F0]">
        <div 
          class="w-16 h-16 rounded-full mx-auto flex items-center justify-center font-bold text-lg mb-2"
          :class="activeThread && activeThread.type === 'personal' ? 'bg-[#E0E7FF] text-[#3730A3]' : 'bg-[#FEF3C7] text-[#92400E]'"
        >
          <template x-if="activeThread && activeThread.avatar_url">
            <img :src="activeThread.avatar_url" class="w-full h-full rounded-full object-cover" alt="" />
          </template>
          <template x-if="activeThread && !activeThread.avatar_url">
            <span x-text="activeThread ? activeThread.initials : ''"></span>
          </template>
        </div>
        <h4 class="font-bold text-sm text-[#111827]" x-text="activeThread ? activeThread.subject : ''"></h4>
        <p class="text-[11px] text-[#475569] mt-0.5" x-text="activeThread && activeThread.other_user ? activeThread.other_user.email : (activeThread ? activeThread.group_type : '')"></p>
      </div>

      <!-- Detail Info -->
      <template x-if="activeThread && activeThread.type === 'personal' && activeThread.other_user">
        <div class="space-y-3">
          <div>
            <span class="text-[10px] font-bold uppercase tracking-wider text-[#94A3B8]">Jabatan / Role</span>
            <p class="text-xs text-[#111827] font-medium capitalize" x-text="activeThread.other_user.role.replace('_', ' ')"></p>
          </div>
          <div>
            <span class="text-[10px] font-bold uppercase tracking-wider text-[#94A3B8]">Departemen</span>
            <p class="text-xs text-[#111827] font-medium" x-text="activeThread.other_user.department"></p>
          </div>
          <div>
            <span class="text-[10px] font-bold uppercase tracking-wider text-[#94A3B8]">Site Penugasan</span>
            <p class="text-xs text-[#111827] font-medium" x-text="activeThread.other_user.site"></p>
          </div>
        </div>
      </template>

      <!-- Group Info & Member Management -->
      <template x-if="activeThread && activeThread.type !== 'personal'">
        <div class="space-y-4">
          <div x-show="activeThread.description">
            <span class="text-[10px] font-bold uppercase tracking-wider text-[#94A3B8]">Deskripsi</span>
            <p class="text-xs text-[#334155] mt-0.5" x-text="activeThread.description"></p>
          </div>

          <!-- Members List -->
          <div>
            <div class="flex items-center justify-between mb-2">
              <span class="text-[10px] font-bold uppercase tracking-wider text-[#94A3B8]" x-text="'Anggota (' + (activeThread.members ? activeThread.members.length : 0) + ')'"></span>
              <template x-if="activeThread.can_manage">
                <button 
                  type="button" 
                  @click="openAddMemberModal()" 
                  class="text-[11px] font-bold text-[#1565D8] hover:underline"
                >
                  + Tambah
                </button>
              </template>
            </div>

            <div class="space-y-2 max-h-48 overflow-y-auto chat-scroll divide-y divide-[#F1F5F9]">
              <template x-for="mem in activeThread.members" :key="mem.id">
                <div class="flex items-center justify-between py-1.5">
                  <div class="flex items-center gap-2 min-w-0">
                    <div class="w-6 h-6 rounded-full bg-[#E2E8F0] flex items-center justify-center font-bold text-[9px] text-[#475569]">
                      <span x-text="mem.name.substring(0, 2).toUpperCase()"></span>
                    </div>
                    <div class="min-w-0">
                      <p class="font-medium text-xs text-[#111827] truncate" x-text="mem.name"></p>
                      <span class="text-[9px] text-[#94A3B8] capitalize" x-text="mem.chat_role"></span>
                    </div>
                  </div>

                  <template x-if="activeThread.can_manage && mem.id !== currentUserId">
                    <button 
                      type="button" 
                      @click="removeGroupMember(mem)" 
                      class="text-[10px] text-[#DC2626] hover:underline"
                    >
                      Hapus
                    </button>
                  </template>
                </div>
              </template>
            </div>
          </div>

          <!-- Leave Group Button -->
          <div class="pt-3 border-t border-[#E2E8F0]">
            <button 
              type="button" 
              @click="leaveActiveGroup()"
              class="w-full py-2 px-3 text-xs font-semibold text-[#DC2626] bg-[#FEF2F2] hover:bg-[#FEE2E2] rounded-lg transition text-center"
            >
              Keluar dari Grup
            </button>
          </div>
        </div>
      </template>

      <!-- Notification Settings -->
      <div class="pt-3 border-t border-[#E2E8F0] space-y-2">
        <span class="text-[10px] font-bold uppercase tracking-wider text-[#94A3B8]">Pengaturan Obrolan</span>
        <button 
          type="button" 
          @click="togglePinCurrentConversation()"
          class="w-full flex items-center justify-between py-1.5 text-xs text-[#111827] hover:text-[#1565D8]"
        >
          <span x-text="activeThread && activeThread.is_pinned ? 'Lepas Sematan (Unpin)' : 'Sematkan Percakapan (Pin)'"></span>
          <x-ui-icon name="pin" class="w-3.5 h-3.5" />
        </button>

        <button 
          type="button" 
          @click="toggleMuteCurrentConversation()"
          class="w-full flex items-center justify-between py-1.5 text-xs text-[#111827] hover:text-[#1565D8]"
        >
          <span x-text="activeThread && activeThread.is_muted ? 'Bunyikan Notifikasi' : 'Senapkan Notifikasi (Mute)'"></span>
          <x-ui-icon name="bell-off" class="w-3.5 h-3.5" />
        </button>
      </div>
    </div>
  </aside>

  <!-- ======================================================== -->
  <!-- PHASE 5 — MODALS                                         -->
  <!-- ======================================================== -->

  <!-- 1. Modal: New Personal Chat -->
  <div 
    x-show="modalNewChat" 
    x-cloak 
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-xs"
  >
    <div 
      @click.outside="modalNewChat = false"
      class="w-full max-w-md bg-white rounded-2xl shadow-xl border border-[#E2E8F0] overflow-hidden flex flex-col max-h-[85vh]"
    >
      <div class="px-5 py-4 border-b border-[#E2E8F0] flex items-center justify-between">
        <div>
          <h3 class="font-bold text-[#111827] text-sm">Mulai Chat Baru</h3>
          <p class="text-xs text-[#475569]">Pilih rekan kerja untuk memulai komunikasi pribadi</p>
        </div>
        <button type="button" @click="modalNewChat = false" class="text-[#94A3B8] hover:text-[#475569]">
          <x-ui-icon name="x" class="w-5 h-5" />
        </button>
      </div>

      <div class="p-4 border-b border-[#E2E8F0]">
        <input 
          type="text" 
          x-model="employeeSearchQuery" 
          @input.debounce.300ms="searchEmployees()"
          placeholder="Cari nama, NIK, atau email..."
          class="w-full px-3 py-2 text-xs bg-[#F8FAFC] border border-[#E2E8F0] rounded-lg text-[#111827] placeholder-[#94A3B8] focus:bg-white focus:border-[#1565D8]"
        />
      </div>

      <div class="flex-1 overflow-y-auto chat-scroll divide-y divide-[#F1F5F9] p-2">
        <p x-show="personalChatError" x-text="personalChatError" role="alert" class="p-3 text-sm text-red-700"></p>
        <template x-for="emp in employeeList" :key="emp.id">
          <button type="button" :disabled="startingPersonalChat" :aria-busy="startingPersonalChat"
            @click="startPersonalChatWith(emp)"
            class="w-full text-left flex items-center gap-3 p-2.5 rounded-lg hover:bg-[#F8FAFC] cursor-pointer transition disabled:opacity-50"
          >
            <div class="w-9 h-9 rounded-full bg-[#E0E7FF] text-[#3730A3] flex items-center justify-center font-bold text-xs shrink-0">
              <span x-text="emp.initials"></span>
            </div>
            <div class="flex-1 min-w-0">
              <p class="text-xs font-semibold text-[#111827] truncate" x-text="emp.name"></p>
              <p class="text-[10px] text-[#475569] truncate" x-text="(emp.nik ? emp.nik + ' · ' : '') + emp.departments"></p>
            </div>
          </button>
        </template>
        <template x-if="employeeList.length === 0">
          <p class="text-xs text-center py-6 text-[#94A3B8]">Pegawai tidak ditemukan.</p>
        </template>
      </div>
    </div>
  </div>

  <!-- 2. Modal: New Group Wizard -->
  <div 
    x-show="modalNewGroup" 
    x-cloak 
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-xs"
  >
    <div 
      @click.outside="modalNewGroup = false"
      class="w-full max-w-lg bg-white rounded-2xl shadow-xl border border-[#E2E8F0] overflow-hidden flex flex-col max-h-[90vh]"
    >
      <div class="px-5 py-4 border-b border-[#E2E8F0] flex items-center justify-between">
        <div>
          <h3 class="font-bold text-[#111827] text-sm">Buat Grup Baru</h3>
          <p class="text-xs text-[#475569]">Kolaborasi tim internal berdasarkan proyek, divisi, atau site</p>
        </div>
        <button type="button" @click="modalNewGroup = false" class="text-[#94A3B8] hover:text-[#475569]">
          <x-ui-icon name="x" class="w-5 h-5" />
        </button>
      </div>

      <div class="p-5 overflow-y-auto chat-scroll space-y-4 text-xs">
        <div>
          <label class="block font-semibold text-[#111827] mb-1">Nama Grup *</label>
          <input 
            type="text" 
            x-model="newGroupData.name" 
            placeholder="Contoh: Operasional Pit 3, HSE Tanggap Darurat..."
            class="w-full px-3 py-2 text-xs bg-[#F8FAFC] border border-[#E2E8F0] rounded-lg focus:bg-white focus:border-[#1565D8]"
          />
        </div>

        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block font-semibold text-[#111827] mb-1">Tipe Grup *</label>
            <select 
              x-model="newGroupData.group_type" 
              class="w-full px-3 py-2 text-xs bg-[#F8FAFC] border border-[#E2E8F0] rounded-lg focus:bg-white focus:border-[#1565D8]"
            >
              <option value="project">Project</option>
              <option value="department">Department</option>
              <option value="site">Site</option>
              <option value="general">General</option>
            </select>
          </div>

          <div>
            <label class="block font-semibold text-[#111827] mb-1">Site Scope</label>
            <select 
              x-model="newGroupData.site_id" 
              class="w-full px-3 py-2 text-xs bg-[#F8FAFC] border border-[#E2E8F0] rounded-lg focus:bg-white focus:border-[#1565D8]"
            >
              <option value="">Semua Site (Lintas)</option>
              @foreach($sites as $site)
                <option value="{{ $site->id }}">{{ $site->code }} — {{ $site->name }}</option>
              @endforeach
            </select>
          </div>
        </div>

        <div>
          <label class="block font-semibold text-[#111827] mb-1">Deskripsi</label>
          <textarea 
            x-model="newGroupData.description" 
            rows="2" 
            placeholder="Tujuan dibuatnya grup ini..."
            class="w-full px-3 py-2 text-xs bg-[#F8FAFC] border border-[#E2E8F0] rounded-lg focus:bg-white focus:border-[#1565D8]"
          ></textarea>
        </div>

        <!-- Member Selection -->
        <div>
          <label class="block font-semibold text-[#111827] mb-1">Pilih Anggota *</label>
          <input 
            type="text" 
            x-model="groupMemberSearch" 
            @input.debounce.300ms="searchEmployeesForGroup()"
            placeholder="Cari anggota untuk ditambahkan..."
            class="w-full px-3 py-1.5 text-xs bg-[#F8FAFC] border border-[#E2E8F0] rounded-lg mb-2"
          />

          <!-- Selected chips -->
          <div class="flex flex-wrap gap-1.5 mb-2" x-show="selectedGroupMembers.length > 0">
            <template x-for="m in selectedGroupMembers" :key="m.id">
              <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-[#EFF6FF] text-[#1565D8] font-medium text-[11px] border border-[#BFDBFE]">
                <span x-text="m.name"></span>
                <button type="button" @click="toggleGroupMember(m)" class="hover:text-[#DC2626]">
                  <x-ui-icon name="x" class="w-3 h-3" />
                </button>
              </span>
            </template>
          </div>

          <!-- Options list -->
          <div class="max-h-36 overflow-y-auto chat-scroll border border-[#E2E8F0] rounded-lg divide-y divide-[#F1F5F9]">
            <template x-for="emp in groupCandidateList" :key="emp.id">
              <div 
                @click="toggleGroupMember(emp)"
                class="flex items-center justify-between p-2 hover:bg-[#F8FAFC] cursor-pointer"
              >
                <div class="min-w-0">
                  <p class="font-medium text-xs text-[#111827]" x-text="emp.name"></p>
                  <p class="text-[10px] text-[#475569]" x-text="emp.departments || emp.email"></p>
                </div>
                <input 
                  type="checkbox" 
                  :checked="selectedGroupMembers.some(x => x.id === emp.id)" 
                  class="rounded border-[#CBD5E1] text-[#1565D8] focus:ring-[#1565D8]" 
                />
              </div>
            </template>
          </div>
        </div>
      </div>

      <div class="px-5 py-3 border-t border-[#E2E8F0] bg-[#F8FAFC] flex justify-end gap-2">
        <button 
          type="button" 
          @click="modalNewGroup = false" 
          class="px-4 py-2 text-xs font-semibold text-[#475569] hover:bg-[#E2E8F0] rounded-lg transition"
        >
          Batal
        </button>
        <button 
          type="button" 
          @click="submitCreateGroup()" 
          :disabled="creatingGroup || !newGroupData.name || selectedGroupMembers.length === 0"
          class="px-4 py-2 text-xs font-semibold text-white bg-[#1565D8] hover:bg-[#0B4DB3] disabled:opacity-50 rounded-lg transition"
        >
          Buat Grup
        </button>
      </div>
    </div>
  </div>

  <!-- 3. Modal: Create Report (Reporting Conversation) -->
  <div 
    x-show="modalReport" 
    x-cloak 
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-xs"
  >
    <div 
      @click.outside="modalReport = false"
      class="w-full max-w-lg bg-white rounded-2xl shadow-xl border border-[#E2E8F0] overflow-hidden flex flex-col max-h-[90vh]"
    >
      <div class="px-5 py-4 border-b border-[#E2E8F0] flex items-center justify-between">
        <div>
          <h3 class="font-bold text-[#111827] text-sm">Buat Laporan Pekerjaan</h3>
          <p class="text-xs text-[#475569]">Kirimkan laporan terstruktur yang dapat dilacak statusnya di chat</p>
        </div>
        <button type="button" @click="modalReport = false" class="text-[#94A3B8] hover:text-[#475569]">
          <x-ui-icon name="x" class="w-5 h-5" />
        </button>
      </div>

      <div class="p-5 overflow-y-auto chat-scroll space-y-4 text-xs">
        <div>
          <label class="block font-semibold text-[#111827] mb-1">Judul Laporan *</label>
          <input 
            type="text" 
            x-model="reportFormData.title" 
            placeholder="Contoh: Temuan KTA di Workshop, Kerusakan Pompa Air Pit..."
            class="w-full px-3 py-2 text-xs bg-[#F8FAFC] border border-[#E2E8F0] rounded-lg focus:bg-white focus:border-[#1565D8]"
          />
        </div>

        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block font-semibold text-[#111827] mb-1">Jenis Laporan *</label>
            <select 
              x-model="reportFormData.report_type" 
              class="w-full px-3 py-2 text-xs bg-[#F8FAFC] border border-[#E2E8F0] rounded-lg focus:bg-white focus:border-[#1565D8]"
            >
              <option value="HSE / Keselamatan">HSE / Keselamatan</option>
              <option value="Maintenance / Perbaikan">Maintenance / Perbaikan</option>
              <option value="Operasional Lapangan">Operasional Lapangan</option>
              <option value="Logistik / Pengadaan">Logistik / Pengadaan</option>
              <option value="Lainnya">Lainnya</option>
            </select>
          </div>

          <div>
            <label class="block font-semibold text-[#111827] mb-1">Prioritas *</label>
            <select 
              x-model="reportFormData.priority" 
              class="w-full px-3 py-2 text-xs bg-[#F8FAFC] border border-[#E2E8F0] rounded-lg focus:bg-white focus:border-[#1565D8]"
            >
              <option value="low">Rendah (Low)</option>
              <option value="medium">Sedang (Medium)</option>
              <option value="high">Tinggi (High)</option>
              <option value="urgent">Mendesak (Urgent)</option>
            </select>
          </div>
        </div>

        <div>
          <label class="block font-semibold text-[#111827] mb-1">Deskripsi Masalah / Laporan *</label>
          <textarea 
            x-model="reportFormData.description" 
            rows="3" 
            placeholder="Jelaskan detail kronologi, lokasi spesifik, atau kebutuhan penanganan..."
            class="w-full px-3 py-2 text-xs bg-[#F8FAFC] border border-[#E2E8F0] rounded-lg focus:bg-white focus:border-[#1565D8]"
          ></textarea>
        </div>
      </div>

      <div class="px-5 py-3 border-t border-[#E2E8F0] bg-[#F8FAFC] flex justify-end gap-2">
        <button 
          type="button" 
          @click="modalReport = false" 
          class="px-4 py-2 text-xs font-semibold text-[#475569] hover:bg-[#E2E8F0] rounded-lg transition"
        >
          Batal
        </button>
        <button 
          type="button" 
          @click="submitCreateReport()" 
          :disabled="submittingReport || !reportFormData.title || !reportFormData.description"
          class="px-4 py-2 text-xs font-semibold text-white bg-[#1565D8] hover:bg-[#0B4DB3] disabled:opacity-50 rounded-lg transition"
        >
          Kirim Laporan
        </button>
      </div>
    </div>
  </div>

  <!-- 4. Modal: Report Details & Update Status -->
  <div 
    x-show="modalReportDetail" 
    x-cloak 
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-xs"
  >
    <div 
      @click.outside="modalReportDetail = false"
      class="w-full max-w-lg bg-white rounded-2xl shadow-xl border border-[#E2E8F0] overflow-hidden flex flex-col max-h-[90vh]"
    >
      <div class="px-5 py-4 border-b border-[#E2E8F0] flex items-center justify-between">
        <h3 class="font-bold text-[#111827] text-sm">Riwayat & Update Laporan</h3>
        <button type="button" @click="modalReportDetail = false" class="text-[#94A3B8] hover:text-[#475569]">
          <x-ui-icon name="x" class="w-5 h-5" />
        </button>
      </div>

      <div class="p-5 overflow-y-auto chat-scroll space-y-4 text-xs" x-if="selectedReport">
        <div>
          <span class="text-[10px] font-bold text-[#1565D8] uppercase tracking-wider" x-text="selectedReport.report_type"></span>
          <h4 class="text-base font-bold text-[#111827]" x-text="selectedReport.title"></h4>
          <p class="text-xs text-[#475569] mt-1" x-text="selectedReport.description"></p>
        </div>

        <div class="p-3 bg-[#F8FAFC] border border-[#E2E8F0] rounded-xl space-y-3">
          <label class="block font-semibold text-[#111827]">Perbarui Status Laporan</label>
          <div class="flex items-center gap-2">
            <select 
              x-model="newReportStatus" 
              class="flex-1 px-3 py-2 text-xs bg-white border border-[#E2E8F0] rounded-lg"
            >
              <option value="open">Open (Terbuka)</option>
              <option value="in_progress">In Progress (Sedang Dikerjakan)</option>
              <option value="resolved">Resolved (Terselesaikan)</option>
              <option value="closed">Closed (Ditutup)</option>
            </select>
            <button 
              type="button" 
              @click="submitUpdateReportStatus()"
              class="px-4 py-2 text-xs font-semibold text-white bg-[#1565D8] hover:bg-[#0B4DB3] rounded-lg transition"
            >
              Update
            </button>
          </div>
        </div>

        <!-- Audit Trail / Status History -->
        <div>
          <span class="text-[10px] font-bold uppercase tracking-wider text-[#94A3B8] block mb-2">Riwayat Perubahan Status</span>
          <div class="space-y-2 border-l-2 border-[#E2E8F0] pl-3">
            <template x-for="hist in (selectedReport.histories || [])" :key="hist.time + hist.to">
              <div class="text-[11px]">
                <p class="font-semibold text-[#111827]">
                  <span class="capitalize" x-text="hist.to.replace('_', ' ')"></span>
                  <span class="text-[#94A3B8] font-normal" x-text="'oleh ' + hist.user + ' · ' + hist.time"></span>
                </p>
                <p x-show="hist.notes" class="text-[#475569] italic" x-text="hist.notes"></p>
              </div>
            </template>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- 5. Context Menu Floating -->
  <div 
    x-show="contextMenu.open" 
    x-cloak 
    @click.outside="contextMenu.open = false"
    :style="`top: ${contextMenu.y}px; left: ${contextMenu.x}px;`"
    class="fixed z-50 w-44 bg-white border border-[#E2E8F0] rounded-xl shadow-lg py-1.5 text-xs text-[#111827]"
  >
    <button 
      type="button" 
      @click="togglePinContextMenu()" 
      class="w-full text-left px-3 py-2 hover:bg-[#F8FAFC] flex items-center justify-between"
    >
      <span x-text="contextMenu.thread && contextMenu.thread.is_pinned ? 'Lepas Pin' : 'Sematkan (Pin)'"></span>
      <x-ui-icon name="pin" class="w-3.5 h-3.5 text-[#1565D8]" />
    </button>
    <button 
      type="button" 
      @click="toggleMuteContextMenu()" 
      class="w-full text-left px-3 py-2 hover:bg-[#F8FAFC] flex items-center justify-between"
    >
      <span x-text="contextMenu.thread && contextMenu.thread.is_muted ? 'Bunyikan' : 'Senyapkan'"></span>
      <x-ui-icon name="bell-off" class="w-3.5 h-3.5 text-[#94A3B8]" />
    </button>
    <button 
      type="button" 
      @click="markAsReadContextMenu()" 
      class="w-full text-left px-3 py-2 hover:bg-[#F8FAFC] flex items-center justify-between"
    >
      <span>Tandai Sudah Dibaca</span>
      <x-ui-icon name="check-check" class="w-3.5 h-3.5 text-[#15803D]" />
    </button>
  </div>

</div>
@endsection

@push('scripts')
<script>
function mandalaChat(config) {
  return {
    currentUserId: config.currentUserId,
    currentUserName: config.currentUserName,
    currentUserRole: config.currentUserRole,
    selectedSiteId: config.selectedSiteId,
    routes: config.routes,

    // State
    conversations: [],
    loadingConversations: true,
    activeThread: null,
    messages: [],
    loadingMessages: false,
    loadingOlderMessages: false,
    startingPersonalChat: false,
    personalChatError: '',
    searchQuery: '',
    activeTab: 'all', // all, personal, group, unread

    // UI Toggles
    infoPanelOpen: false,
    showScrollDown: false,
    replyingTo: null,
    messageBody: '',
    pendingAttachments: [],
    sendingMessage: false,

    // Modals
    modalNewChat: false,
    modalNewGroup: false,
    modalReport: false,
    modalReportDetail: false,
    employeeSearchQuery: '',
    employeeList: [],
    groupMemberSearch: '',
    groupCandidateList: [],
    selectedGroupMembers: [],
    newGroupData: {
      name: '',
      group_type: 'project',
      site_id: '',
      description: ''
    },
    creatingGroup: false,

    // Report Form
    reportFormData: {
      title: '',
      report_type: 'HSE / Keselamatan',
      priority: 'medium',
      description: ''
    },
    submittingReport: false,
    selectedReport: null,
    newReportStatus: 'open',

    // Context Menu
    contextMenu: {
      open: false,
      x: 0,
      y: 0,
      thread: null,
    },

    // Polling timer
    pollTimer: null,

    initChat() {
      this.fetchConversations().then(() => {
        if (config.activeThreadId) {
          const match = this.conversations.find(c => c.id === config.activeThreadId);
          if (match) {
            this.selectConversation(match);
          } else {
            // Load direct by id
            this.loadThreadById(config.activeThreadId);
          }
        }
      });

      // Background sync every 4 seconds for fresh messages (Phase 6 Realtime fallback)
      this.pollTimer = setInterval(() => {
        if (this.activeThread) {
          this.syncActiveThreadMessages();
        }
      }, 4000);
    },

    async fetchConversations() {
      this.loadingConversations = true;
      try {
        const params = new URLSearchParams({
          tab: this.activeTab,
          q: this.searchQuery,
          site_id: this.selectedSiteId || '',
        });
        const res = await fetch(`${this.routes.conversations}?${params.toString()}`);
        const data = await res.json();
        if (data.success) {
          this.conversations = data.data;
        }
      } catch (e) {
        console.error('Failed to load conversations', e);
      } finally {
        this.loadingConversations = false;
      }
    },

    async selectConversation(conv) {
      this.activeThread = conv;
      this.messages = [];
      this.loadingMessages = true;
      this.replyingTo = null;

      try {
        const res = await fetch(`/admin/qa/${conv.id}/messages`);
        const data = await res.json();
        if (data.success) {
          this.activeThread = { ...this.activeThread, ...data.thread };
          this.messages = data.messages;
          this.$nextTick(() => {
            this.scrollToBottom(false);
          });
        }
      } catch (e) {
        console.error('Failed to load messages', e);
      } finally {
        this.loadingMessages = false;
      }
    },

    async loadThreadById(threadId) {
      this.loadingMessages = true;
      this.replyingTo = null;
      try {
        const res = await fetch(`/admin/qa/${threadId}/messages`, { headers: { 'Accept': 'application/json' } });
        if (!res.ok) throw new Error('Ruang chat gagal dimuat. Silakan coba lagi.');
        const data = await res.json();
        if (!data.success || !data.thread) throw new Error('Ruang chat gagal dimuat. Silakan coba lagi.');
        if (data.success) {
          this.activeThread = data.thread;
          this.messages = data.messages;
          this.$nextTick(() => {
            this.scrollToBottom(false);
          });
        }
      } catch (e) {
        throw e;
      } finally {
        this.loadingMessages = false;
      }
    },

    async syncActiveThreadMessages() {
      if (!this.activeThread) return;
      try {
        const res = await fetch(`/admin/qa/${this.activeThread.id}/messages`);
        const data = await res.json();
        if (data.success) {
          const prevLength = this.messages.length;
          this.messages = data.messages;
          // If new message came in and user is near bottom, scroll down
          if (data.messages.length > prevLength && !this.showScrollDown) {
            this.$nextTick(() => this.scrollToBottom(true));
          }
        }
      } catch (e) {}
    },

    handleScroll() {
      const el = this.$refs.messageList;
      if (!el) return;
      const distanceFromBottom = el.scrollHeight - el.scrollTop - el.clientHeight;
      this.showScrollDown = distanceFromBottom > 150;
    },

    scrollToBottom(smooth = true) {
      const el = this.$refs.messageList;
      if (el) {
        el.scrollTo({
          top: el.scrollHeight,
          behavior: smooth ? 'smooth' : 'instant',
        });
      }
    },

    handleEnterKey(e) {
      if (!e.shiftKey) {
        e.preventDefault();
        this.sendMessage();
      }
    },

    handleFileSelect(e) {
      const files = Array.from(e.target.files);
      this.pendingAttachments.push(...files);
    },

    removePendingAttachment(idx) {
      this.pendingAttachments.splice(idx, 1);
    },

    async sendMessage() {
      if (!this.activeThread) return;
      if (this.messageBody.trim() === '' && this.pendingAttachments.length === 0) return;

      this.sendingMessage = true;
      const formData = new FormData();
      formData.append('body', this.messageBody);
      if (this.replyingTo) {
        formData.append('parent_id', this.replyingTo.id);
      }
      this.pendingAttachments.forEach((f) => {
        formData.append('attachments[]', f);
      });

      // Optimistic update
      const tempId = 'temp_' + Date.now();
      const optimisticMsg = {
        id: tempId,
        user_id: this.currentUserId,
        user_name: this.currentUserName,
        user_initials: this.currentUserName.substring(0, 2).toUpperCase(),
        is_mine: true,
        body: this.messageBody,
        message_type: 'text',
        attachments: [],
        parent: this.replyingTo ? { user_name: this.replyingTo.user_name, body: this.replyingTo.body } : null,
        reactions: [],
        created_at: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
        created_date: new Date().toISOString().split('T')[0],
        created_date_label: 'Hari Ini',
      };
      this.messages.push(optimisticMsg);
      this.messageBody = '';
      this.replyingTo = null;
      this.pendingAttachments = [];
      this.$nextTick(() => this.scrollToBottom(true));

      try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const res = await fetch(`/admin/qa/${this.activeThread.id}/messages`, {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': csrfToken,
          },
          body: formData,
        });
        const data = await res.json();
        if (data.success) {
          // Replace optimistic message
          const idx = this.messages.findIndex(m => m.id === tempId);
          if (idx !== -1) {
            this.messages[idx] = data.message;
          }
        }
      } catch (e) {
        console.error('Failed to send message', e);
      } finally {
        this.sendingMessage = false;
        this.fetchConversations();
      }
    },

    setReplyTo(msg) {
      this.replyingTo = msg;
      this.$nextTick(() => {
        this.$refs.messageInput?.focus();
      });
    },

    copyMessage(msg) {
      if (msg.body) {
        navigator.clipboard.writeText(msg.body);
        alert('Teks berhasil disalin');
      }
    },

    async toggleReaction(msg, emoji) {
      try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        await fetch(`/admin/qa/messages/${msg.id}/reactions`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
          },
          body: JSON.stringify({ reaction: emoji }),
        });
        this.syncActiveThreadMessages();
      } catch (e) {
        console.error(e);
      }
    },

    async togglePinMessage(msg) {
      try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        await fetch(`/admin/qa/messages/${msg.id}/pin`, {
          method: 'POST',
          headers: { 'X-CSRF-TOKEN': csrfToken },
        });
        this.selectConversation(this.activeThread);
      } catch (e) {
        console.error(e);
      }
    },

    openEditMessageModal(msg) {
      const newBody = prompt('Edit pesan Anda:', msg.body);
      if (newBody !== null && newBody.trim() !== '') {
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        fetch(`/admin/qa/messages/${msg.id}`, {
          method: 'PUT',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
          },
          body: JSON.stringify({ body: newBody }),
        }).then(() => this.syncActiveThreadMessages());
      }
    },

    openDeleteMessageModal(msg) {
      if (confirm('Apakah Anda yakin ingin menghapus pesan ini?')) {
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        fetch(`/admin/qa/messages/${msg.id}`, {
          method: 'DELETE',
          headers: { 'X-CSRF-TOKEN': csrfToken },
        }).then(() => {
          this.messages = this.messages.filter(m => m.id !== msg.id);
        });
      }
    },

    // New Chat Modal
    openNewChatModal() {
      this.modalNewChat = true;
      this.personalChatError = '';
      this.employeeSearchQuery = '';
      this.searchEmployees();
    },

    async searchEmployees() {
      try {
        const params = new URLSearchParams({
          q: this.employeeSearchQuery,
          site_id: this.selectedSiteId || '',
        });
        const res = await fetch(`${this.routes.searchEmployees}?${params.toString()}`);
        const data = await res.json();
        if (data.success) {
          this.employeeList = data.data;
        }
      } catch (e) {
        console.error(e);
      }
    },

    async startPersonalChatWith(emp) {
      if (this.startingPersonalChat) return;
      this.startingPersonalChat = true;
      this.personalChatError = '';
      try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const res = await fetch(this.routes.startPersonal, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
          },
          body: JSON.stringify({ recipient_id: emp.id }),
        });
        if (!res.ok) throw new Error('Chat belum dapat dibuka. Muat ulang halaman dan coba lagi.');
        const data = await res.json();
        if (data.success && data.thread_id) {
          // Immediately set active thread info
          await this.loadThreadById(data.thread_id);
          this.modalNewChat = false;
          await this.fetchConversations();
          this.$nextTick(() => {
            this.$refs.messageInput?.focus();
          });
        } else throw new Error('Chat belum dapat dibuka. Silakan coba lagi.');
      } catch (e) {
        this.personalChatError = e.message || 'Chat belum dapat dibuka. Silakan coba lagi.';
      } finally {
        this.startingPersonalChat = false;
      }
    },

    // New Group Wizard
    openNewGroupModal() {
      this.modalNewGroup = true;
      this.newGroupData = {
        name: '',
        group_type: 'project',
        site_id: this.selectedSiteId || '',
        description: '',
      };
      this.selectedGroupMembers = [];
      this.searchEmployeesForGroup();
    },

    async searchEmployeesForGroup() {
      try {
        const params = new URLSearchParams({
          q: this.groupMemberSearch,
          site_id: this.newGroupData.site_id || '',
        });
        const res = await fetch(`${this.routes.searchEmployees}?${params.toString()}`);
        const data = await res.json();
        if (data.success) {
          this.groupCandidateList = data.data;
        }
      } catch (e) {
        console.error(e);
      }
    },

    toggleGroupMember(emp) {
      const idx = this.selectedGroupMembers.findIndex(m => m.id === emp.id);
      if (idx !== -1) {
        this.selectedGroupMembers.splice(idx, 1);
      } else {
        this.selectedGroupMembers.push(emp);
      }
    },

    async submitCreateGroup() {
      if (!this.newGroupData.name || this.selectedGroupMembers.length === 0) return;
      this.creatingGroup = true;
      try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const res = await fetch(this.routes.createGroup, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
          },
          body: JSON.stringify({
            name: this.newGroupData.name,
            group_type: this.newGroupData.group_type,
            site_id: this.newGroupData.site_id || null,
            description: this.newGroupData.description,
            member_ids: this.selectedGroupMembers.map(m => m.id),
          }),
        });
        const data = await res.json();
        if (data.success) {
          this.modalNewGroup = false;
          await this.fetchConversations();
          this.loadThreadById(data.thread_id);
        }
      } catch (e) {
        console.error(e);
      } finally {
        this.creatingGroup = false;
      }
    },

    // Report Modals
    openReportModal() {
      this.modalReport = true;
      this.reportFormData = {
        title: '',
        report_type: 'HSE / Keselamatan',
        priority: 'medium',
        description: '',
      };
    },

    async submitCreateReport() {
      if (!this.activeThread) return;
      this.submittingReport = true;
      try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const res = await fetch(`/admin/qa/${this.activeThread.id}/reports`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
          },
          body: JSON.stringify({
            title: this.reportFormData.title,
            report_type: this.reportFormData.report_type,
            priority: this.reportFormData.priority,
            description: this.reportFormData.description,
            site_id: this.activeThread.site?.id || null,
          }),
        });
        const data = await res.json();
        if (data.success) {
          this.modalReport = false;
          this.syncActiveThreadMessages();
          this.fetchConversations();
        }
      } catch (e) {
        console.error(e);
      } finally {
        this.submittingReport = false;
      }
    },

    openReportDetailModal(report) {
      this.selectedReport = report;
      this.newReportStatus = report.status;
      this.modalReportDetail = true;
    },

    async submitUpdateReportStatus() {
      if (!this.selectedReport) return;
      try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const res = await fetch(`/admin/qa/reports/${this.selectedReport.id}/status`, {
          method: 'PUT',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
          },
          body: JSON.stringify({
            status: this.newReportStatus,
            notes: 'Status diperbarui melalui Mandala Chat',
          }),
        });
        const data = await res.json();
        if (data.success) {
          this.modalReportDetail = false;
          this.syncActiveThreadMessages();
        }
      } catch (e) {
        console.error(e);
      }
    },

    // Context Menu Handling
    openContextMenu(e, conv) {
      this.contextMenu = {
        open: true,
        x: Math.min(e.clientX, window.innerWidth - 180),
        y: Math.min(e.clientY, window.innerHeight - 150),
        thread: conv,
      };
    },

    async togglePinContextMenu() {
      if (!this.contextMenu.thread) return;
      const action = this.contextMenu.thread.is_pinned ? 'unpin' : 'pin';
      this.contextMenu.open = false;
      const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
      await fetch(`/admin/qa/${this.contextMenu.thread.id}/settings`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
        body: JSON.stringify({ action }),
      });
      this.fetchConversations();
    },

    async toggleMuteContextMenu() {
      if (!this.contextMenu.thread) return;
      const action = this.contextMenu.thread.is_muted ? 'unmute' : 'mute';
      this.contextMenu.open = false;
      const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
      await fetch(`/admin/qa/${this.contextMenu.thread.id}/settings`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
        body: JSON.stringify({ action }),
      });
      this.fetchConversations();
    },

    async markAsReadContextMenu() {
      if (!this.contextMenu.thread) return;
      this.contextMenu.open = false;
      const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
      await fetch(`/admin/qa/${this.contextMenu.thread.id}/settings`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
        body: JSON.stringify({ action: 'read' }),
      });
      this.fetchConversations();
    },

    async togglePinCurrentConversation() {
      if (!this.activeThread) return;
      const action = this.activeThread.is_pinned ? 'unpin' : 'pin';
      const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
      await fetch(`/admin/qa/${this.activeThread.id}/settings`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
        body: JSON.stringify({ action }),
      });
      this.activeThread.is_pinned = !this.activeThread.is_pinned;
      this.fetchConversations();
    },

    async toggleMuteCurrentConversation() {
      if (!this.activeThread) return;
      const action = this.activeThread.is_muted ? 'unmute' : 'mute';
      const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
      await fetch(`/admin/qa/${this.activeThread.id}/settings`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
        body: JSON.stringify({ action }),
      });
      this.activeThread.is_muted = !this.activeThread.is_muted;
      this.fetchConversations();
    },

    async leaveActiveGroup() {
      if (!this.activeThread) return;
      if (confirm('Apakah Anda yakin ingin keluar dari grup ini?')) {
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        await fetch(`/admin/qa/${this.activeThread.id}/leave`, {
          method: 'POST',
          headers: { 'X-CSRF-TOKEN': csrfToken },
        });
        this.activeThread = null;
        this.infoPanelOpen = false;
        this.fetchConversations();
      }
    },

    async removeGroupMember(mem) {
      if (!this.activeThread) return;
      if (confirm(`Keluarkan ${mem.name} dari grup?`)) {
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        await fetch(`/admin/qa/${this.activeThread.id}/members`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
          body: JSON.stringify({ action: 'remove', user_id: mem.id }),
        });
        this.selectConversation(this.activeThread);
      }
    },

    closeAllModals() {
      this.modalNewChat = false;
      this.modalNewGroup = false;
      this.modalReport = false;
      this.modalReportDetail = false;
      this.contextMenu.open = false;
    }
  }
}
</script>
@endpush
