FROM rabbitmq:latest

# https://github.com/rabbitmq/rabbitmq-delayed-message-exchange/releases
COPY ./rabbitmq_delayed_message_exchange-3.9.0.ez $RABBITMQ_HOME/plugins/rabbitmq_delayed_message_exchange-3.9.0.ez
RUN chown rabbitmq:rabbitmq $RABBITMQ_HOME/plugins/rabbitmq_delayed_message_exchange-3.9.0.ez

RUN rabbitmq-plugins enable --offline rabbitmq_delayed_message_exchange
RUN rabbitmq-plugins enable --offline rabbitmq_consistent_hash_exchange
RUN rabbitmq-plugins enable --offline rabbitmq_management