
@props([
    'loadedMessages'=>$loadedMessages,
    'receiver'=>$receiver
])
<main x-data="{
    canLoadMore:@entangle('canLoadMore')
}" @scroll="
scrollTop= $el.scrollTop;


if((scrollTop<=0) && canLoadMore){
    await $nextTick();
    $wire.loadMore();
}

" @update-height.window="

    await $nextTick();
            newHeight=$el.scrollHeight;

            oldHeight= height;

            $el.scrollTop=newHeight- oldHeight;

            height=newHeight;


" id="conversation"
class="flex flex-col  gap-2 gap-y-4   p-2.5  overflow-y-auto flex-grow  overscroll-contain overflow-x-hidden w-full my-auto "
style="contain: content"
:class="{'invisible': initializing, 'visible': !initializing}"
>


{{-- <div x-cloak wire:loading.flex wire:target="loadMore()" class="hidden w-full  items-center py-2 ">
    <div class="mx-auto ">
        <svg aria-hidden="true" class="w-5 h-5 text-gray-200 animate-spin dark:text-gray-600 fill-blue-600"
            viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path
                d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z"
                fill="currentColor" />
            <path
                d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z"
                fill="currentFill" />
        </svg>
    </div>
</div> --}}

<div x-cloak wire:loading.delay.class.remove="invisible" wire:target="loadMore"
    class="invisible transition-all duration-300 ">
    <x-wirechat::loading-spin />
</div>


{{-- Define previous message outside the loop --}}
@php
$previousMessage=null;

@endphp

<!--Message-->
@if ($loadedMessages)
    
@foreach ($loadedMessages as $key=> $message)


    @php
        $belongsToAuth= $message->belongsToAuth();
        $parent =$message->parent??null;
        $attachment= $message->attachment??null;
        $isEmoji =mb_ereg('^(?:\X(?=\p{Emoji}))*\X$', $message->body??'');

        // keep track of previous message
        // The ($key -1 ) will get the previous message from loaded
        // messages since $key is directly linked to $message

        if ($key > 0){
        $previousMessage = $loadedMessages->get($key - 1) ;
        }

        // Get the next message
        $nextMessage = ($key < $loadedMessages->count() - 1) ? $loadedMessages->get($key + 1) : null;
    @endphp

    <div wire:key="message-{{$key}}" @class([ 'max-w-[85%] md:max-w-[78%]  flex flex-col gap-y-2  ' , 'ml-auto'=>$belongsToAuth])>

        {{-- Show parent/reply message --}}
        @if ($parent!=null)

            <div @class(['  max-w-fit   flex flex-col gap-y-2', 'ml-auto' => $belongsToAuth,'ml-9 sm:ml-10'=>!$belongsToAuth]) >

                <h6 class="text-xs text-gray-500 dark:text-gray-300 px-2 ">
                    {{$message?->ownedBy(auth()->user())?'You ':$message->sendable?->display_name??'User'}} replied to  {{$parent?->ownedBy($receiver)?($message?->ownedBy($receiver)?'Themself': $receiver->display_name):($message?->ownedBy(auth()->user())?'Yourself':" You")}}
                </h6>

                <div @class(['px-1 dark:border-gray-500 overflow-hidden ', ' border-r-4 ml-auto' => $belongsToAuth,' border-l-4 mr-auto ' => !$belongsToAuth]) >
                    <p
                        class=" bg-gray-100 dark:text-white  dark:bg-gray-600 text-black line-clamp-1 text-sm sm:text-base  rounded-full max-w-fit   px-3 py-1 ">
                        {{$parent?->body!=''?$parent?->body:($parent->hasAttachment()?'Attachment':'')}}
                    </p>
                </div>


            </div>

        @endif



        {{-- Body section --}}
        <div @class(['flex gap-1 md:gap-4 group transition-transform ',' justify-end'=>$belongsToAuth])>

            {{-- Actions --}}
            <div @class([ 'my-auto flex invisible w-auto  items-center gap-2 group-hover:visible' , 'order-1'=>!$belongsToAuth])>

                <button wire:click="setReply('{{$message->id}}')" class="hover:scale-110 transition-transform">
                    {{-- <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7"
                        stroke="currentColor" class="w-4 h-4 text-gray-600/80 dark:text-white">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3" />
                    </svg> --}}
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-reply-fill w-4 h-4 dark:text-white" viewBox="0 0 16 16">
                        <path d="M5.921 11.9 1.353 8.62a.72.72 0 0 1 0-1.238L5.921 4.1A.716.716 0 0 1 7 4.719V6c1.5 0 6 0 7 8-2.5-4.5-7-4-7-4v1.281c0 .56-.606.898-1.079.62z"/>
                      </svg>
                </button>

                <x-wirechat::dropdown align="{{$belongsToAuth?'right':'left'}}" width="48">
                    <x-slot name="trigger">
                        {{-- Dots --}}
                        <button class="hover:scale-110 transition-transform">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                                class="bi bi-three-dots h-3 w-3 text-gray-700 dark:text-white" viewBox="0 0 16 16">
                                <path
                                    d="M3 9.5a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3m5 0a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3m5 0a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3" />
                            </svg>
                        </button>
                    </x-slot>
                    <x-slot name="content">

                        @if ($message->ownedBy(auth()->user()))
                        <button wire:click="deleteForEveryone('{{$message->id}}')" wire:confirm="are you sure" class="w-full text-start">
                            <x-wirechat::dropdown-link>
                                Delete for everyone
                            </x-wirechat::dropdown-link>
                        </button>
                        @endif


                        <button wire:click="deleteForMe('{{$message->id}}')" wire:confirm="are you sure" class="w-full text-start">
                            <x-wirechat::dropdown-link>
                                Delete for me
                            </x-wirechat::dropdown-link>
                        </button>

                    </x-slot>
                </x-wirechat::dropdown>

            </div>

            {{--Message user Avatar --}}
            {{--Hide avatar if message belongs to auth --}}
            @if (!$belongsToAuth)
                <div @class([
                    'shrink-0 mt-auto -mb-2',
                    // Hide avatar if the next message is from the same user
                    'invisible' => ($nextMessage && $message->sendable_id == $nextMessage->sendable_id && $message->sendable_type == $nextMessage->sendable_type)
                ])>                       
                    <x-wirechat::avatar src="{{$receiver->cover_url ?? null}}" class="h-7 w-7" />
                </div>
            @endif
         

            {{-- Message body --}}
            <div class="flex flex-col gap-2 max-w-[95%] ">

                {{------------------------}}
                {{-- Attachment section --}}
                {{------------------------}}

                @if ($attachment)

                {{-- Attachemnt is Application/ --}}
                @if (str()->startsWith($attachment->mime_type, 'application/'))
                <x-wirechat::chat.file :attachment="$attachment" />
                @endif

                {{-- Attachemnt is Video/ --}}
                @if (str()->startsWith($attachment->mime_type, 'video/'))
                <x-wirechat::chat.video height="max-h-[400px]" :cover="false"
                    source="{{url('storage/' . $attachment?->file_path) }}" />
                @endif

                {{-- Attachemnt is image/ --}}
                @if (str()->startsWith($attachment->mime_type, 'image/'))
                <x-wirechat::chat.image :previousMessage="$previousMessage" :message="$message"
                    :nextMessage="$nextMessage" :belongsToAuth="$belongsToAuth" :attachment="$attachment" />

                @endif
                @endif

                {{-- if message is emoji then don't show the styled messagebody layout --}}
                @if ($isEmoji)
                <p class="text-5xl dark:text-white ">
                    {{$message->body}}
                </p>
                @endif

                {{------------------------}}
                {{--Message body section--}}
                {{--If message is not emoji then show the message body styles --}}
                {{------------------------}}

                @if ($message->body && !$isEmoji)
                <x-wirechat::chat.message :previousMessage="$previousMessage" :message="$message"
                    :nextMessage="$nextMessage" :belongsToAuth="$belongsToAuth" :attachment="$attachment" />
                @endif

            </div>

        </div>
    </div>

@endforeach
@endif

</main>