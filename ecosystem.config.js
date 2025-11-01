module.exports = {
    apps : [{
        name   : 'thinkphp-queue-worker',
        cwd: `${__dirname}/`,
        interpreter: 'php',
        script : 'think',
        args   : 'queue:work',
        instances: 1,
        autorestart: true,
        watch: false,
        user: 'www',
        max_memory_restart: '1G',
        log_date_format: 'YYYY-MM-DDTHH:mm:ss.SSS',
        error: './runtime/log/queue_error.log',
        output: './runtime/log/queue_out.log',
        env: {
            NODE_ENV: 'development'
        },
        env_production: {
            NODE_ENV: 'production'
        }
    }]
}