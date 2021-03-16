<p style="color: #484848; font-size: 16px; font-weight: normal; font-family: Helvetica, Arial, sans-serif;">Payment Refno #{$this->refno} - {$this->response}</p>

	<ul>
		{foreach $this->err as $v}
			<li>{$v}</li>
		{/foreach}
	</ul>