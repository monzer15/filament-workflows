<x-filament::page>

    {{ $this->form }}

</x-filament::page>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        console.log('DOMContentLoaded.load')
        setTimeout(function (){
            var logs = document.getElementById('logs');
            logs.scrollTop = logs.scrollHeight;

            var execution_logs = document.getElementById('execution_logs');
            execution_logs.scrollTop = execution_logs.scrollHeight;
        }, 200)
    });
</script>
