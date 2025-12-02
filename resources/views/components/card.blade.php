<div {{ $attributes->merge([
    'class' => 'card shadow-sm p-4 rounded-xl bg-white mb-4'
]) }}>
    {{ $slot }}
</div>

<style>
.card {
    border: 1px solid #f0f0f0;
    box-shadow: 0 4px 18px rgba(0,0,0,0.04);
    border-radius: 14px;
}
</style>
