<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE xsl:stylesheet SYSTEM "lang://56">
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:hostcms="http://www.hostcms.ru/"
	exclude-result-prefixes="hostcms">
	<xsl:output xmlns="http://www.w3.org/TR/xhtml1/strict" doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN" encoding="utf-8" indent="yes" method="html" omit-xml-declaration="no" version="1.0" media-type="text/xml"/>
	
	<!-- МагазинТовар -->
	
	<xsl:decimal-format name="my" decimal-separator="," grouping-separator=" "/>
	
	<xsl:template match="/shop">
		<xsl:apply-templates select="shop_item"/>
		
		<!-- Есть просмотренные товары -->
		<xsl:if test="viewed/shop_item">
			<p class="h1 red">&labelViewedItems;</p>
			<div class="shop_block">
				<div class="shop_table">
					<!-- Выводим товары магазина -->
					<xsl:apply-templates select="viewed/shop_item[position() &lt; 4]" mode="view"/>
				</div>
			</div>
		</xsl:if>
	</xsl:template>
	
	<xsl:template match="shop_item">
		
		<h1 hostcms:id="{@id}" hostcms:field="name" hostcms:entity="shop_item"><xsl:value-of select="name"/></h1>
		
		<!-- Store parent id in a variable -->
		<xsl:variable name="group" select="/shop/group"/>
		
		<p>
			<xsl:if test="$group = 0">
				<a href="{/shop/url}" hostcms:id="{/shop/@id}" hostcms:field="name" hostcms:entity="shop">
					<xsl:value-of select="/shop/name"/>
				</a>
			</xsl:if>
			
			<!-- Breadcrumbs -->
			<xsl:apply-templates select="/shop//shop_group[@id=$group]" mode="breadCrumbs"/>
			
			<!-- Если модификация, выводим в пути родительский товар -->
			<xsl:if test="shop_item/node()">
			<span><xsl:text> → </xsl:text></span>
				<a href="{shop_item/url}">
					<xsl:value-of disable-output-escaping="yes" select="shop_item/name"/>
				</a>
			</xsl:if>
			
		<span><xsl:text> → </xsl:text></span>
			
		<b><a href="{url}" hostcms:id="{@id}" hostcms:field="name" hostcms:entity="shop_item"><xsl:value-of select="name"/></a></b>
		</p>
		
		<!-- Show Message -->
		<xsl:if test="/shop/message/node()">
			<xsl:value-of disable-output-escaping="yes" select="/shop/message"/>
		</xsl:if>
		
		<div>
			<!-- Изображение для товара, если есть -->
			<xsl:if test="image_small != ''">
				<div id="gallery" class="shop_img">
					<a href="{dir}{image_large}" target="_blank"><img src="{dir}{image_small}" /></a>
				</div>
			</xsl:if>
			
			<!-- Цена товара -->
			<xsl:if test="price != 0">
				<div class="price">
				<xsl:value-of select="format-number(price, '### ##0,00', 'my')"/><xsl:text> </xsl:text><xsl:value-of select="currency"/><xsl:text> </xsl:text>
					
					<!-- Если цена со скидкой - выводим ее -->
					<xsl:if test="discount != 0">
						<span class="oldPrice">
							<xsl:value-of select="format-number(price + discount, '### ##0,00', 'my')"/><xsl:text> </xsl:text><xsl:value-of select="currency" />
					</span><xsl:text> </xsl:text>
					</xsl:if>
					
					<!-- Ссылку на добавление в корзины выводим, если:
					type = 0 - простой тип товара
					type = 1 - электронный товар, при этом остаток на складе больше 0 или -1,
					что означает неограниченное количество -->
					<xsl:if test="type != 1 or digitals > 0 or digitals = -1">
						<a href="{/shop/url}cart/?add={@id}" onclick="return $.addIntoCart('{/shop/url}cart/', {@id}, 1)">
							<img src="/images/add_to_cart.gif" alt="&labelAddIntoCart;" title="&labelAddIntoCart;" />
						</a>
					</xsl:if>
				</div>
			</xsl:if>
			
			<!-- Cкидки -->
			<xsl:if test="count(shop_discount)">
				<xsl:apply-templates select="shop_discount"/>
			</xsl:if>
			
			<xsl:if test="marking != ''">
			<div class="shop_property">&labelMarking; <span hostcms:id="{@id}" hostcms:field="marking" hostcms:entity="shop_item"><xsl:value-of select="marking"/></span></div>
			</xsl:if>
			
			<xsl:if test="shop_producer/node()">
			<div class="shop_property">&labelProducer; <span><xsl:value-of select="shop_producer/name"/></span></div>
			</xsl:if>
			
			<!-- Если указан вес товара -->
			<xsl:if test="weight != 0">
	<div class="shop_property">&labelWeight; <span hostcms:id="{@id}" hostcms:field="weight" hostcms:entity="shop_item"><xsl:value-of select="weight"/></span><xsl:text> </xsl:text><span><xsl:value-of select="/shop/shop_measure/name"/></span></div>
			</xsl:if>
			
			<!-- Количество на складе для не электронного товара -->
			<xsl:if test="rest &gt; 0 and type != 1">
<div class="shop_property">&labelRest; <span><xsl:value-of select="rest - reserved"/><xsl:text> </xsl:text><xsl:value-of select="shop_measure/name"/></span><xsl:if test="reserved &gt; 0"> (&labelReserve; <span><xsl:value-of select="reserved"/><xsl:text> </xsl:text><xsl:value-of select="shop_measure/name"/></span>)</xsl:if></div>
			</xsl:if>
			
			<!-- Если электронный товар, выведим доступное количество -->
			<xsl:if test="type = 1">
				<div class="shop_property">
					<xsl:choose>
						<xsl:when test="digitals = 0">
							&labelDigital0;
						</xsl:when>
						<xsl:when test="digitals = -1">
							&labelDigital1;
						</xsl:when>
						<xsl:otherwise>
					&labelWarehouseRest; <span><xsl:value-of select="digitals" /><xsl:text> </xsl:text><xsl:value-of select="shop_measure/name" /></span>
						</xsl:otherwise>
					</xsl:choose>
				</div>
			</xsl:if>
			
			<div style="clear: both;"></div>
			
			<!-- Описание товара -->
			<xsl:if test="description != ''">
				<div hostcms:id="{@id}" hostcms:field="description" hostcms:entity="shop_item" hostcms:type="wysiwyg"><xsl:value-of disable-output-escaping="yes" select="description" /></div>
			</xsl:if>
			
			<!-- Текст товара -->
			<xsl:if test="text != ''">
				<div hostcms:id="{@id}" hostcms:field="text" hostcms:entity="shop_item" hostcms:type="wysiwyg"><xsl:value-of disable-output-escaping="yes" select="text"/></div>
			</xsl:if>
			
			<!-- Размеры товара -->
			<xsl:if test="length != 0 or width != 0 or height != 0">
				<div class="shop_property">&labelSizes; <span><xsl:value-of select="length" /><xsl:text> </xsl:text><xsl:value-of select="/shop/size_measure/name" />
						<xsl:text> × </xsl:text>
						<xsl:value-of select="width" /><xsl:text> </xsl:text><xsl:value-of select="/shop/size_measure/name" />
						<xsl:text> × </xsl:text>
				<xsl:value-of select="height" /><xsl:text> </xsl:text><xsl:value-of select="/shop/size_measure/name" /></span></div>
			</xsl:if>
			
			<xsl:if test="count(property_value)">
				<h2>&labelAttributes;</h2>
				<xsl:apply-templates select="property_value"/>
			</xsl:if>
		</div>
		
		<!-- Модификации -->
		<xsl:if test="count(modifications/shop_item) &gt; 0">
			<p class="h2">&labelModifiations; <xsl:value-of select="name"/></p>
			<ul class="shop_list">
				<xsl:apply-templates select="modifications/shop_item"/>
			</ul>
		</xsl:if>
		
		<xsl:if test="count(associated/shop_item) &gt; 0">
			<p class="h2">&labelTying; <xsl:value-of select="name"/></p>
			<ul class="shop_list">
				<xsl:apply-templates select="associated/shop_item"/>
			</ul>
		</xsl:if>
		
		<p class="tags">
			<!-- Average Grade -->
			<xsl:if test="comments_average_grade/node() and comments_average_grade != 0">
				<span><xsl:call-template name="show_average_grade">
						<xsl:with-param name="grade" select="comments_average_grade"/>
					<xsl:with-param name="const_grade" select="5"/></xsl:call-template></span>
			</xsl:if>
			
			<!-- Processing of the selected tag -->
			<xsl:if test="count(tag)">
				<img src="/images/tag.png" /><span><xsl:apply-templates select="tag"/></span>
			</xsl:if>
			
			<xsl:if test="count(siteuser) &gt; 0">
			<img src="/images/user.png" /><span><a href="/users/info/{siteuser/path}/"><xsl:value-of select="siteuser/login"/></a></span>
			</xsl:if>
			
			<xsl:if test="rate/node()">
				<span id="shop_item_id_{@id}" class="thumbs">
					<xsl:choose>
						<xsl:when test="/shop/siteuser_id > 0">
							<xsl:choose>
								<xsl:when test="vote/value = 1">
									<xsl:attribute name="class">thumbs up</xsl:attribute>
								</xsl:when>
								<xsl:when test="vote/value = -1">
									<xsl:attribute name="class">thumbs down</xsl:attribute>
								</xsl:when>
							</xsl:choose>
							<span id="shop_item_likes_{@id}"><xsl:value-of select="rate/@likes" /></span>
							<span class="inner_thumbs">
								<a onclick="return $.sendVote({@id}, 1, 'shop_item')" href="{/shop/url}?id={@id}&amp;vote=1&amp;entity_type=shop_item" alt="&labelLike;"></a>
								<span class="rate" id="shop_item_rate_{@id}"><xsl:value-of select="rate" /></span>
								<a onclick="return $.sendVote({@id}, 0, 'shop_item')" href="{/shop/url}?id={@id}&amp;vote=0&amp;entity_type=shop_item" alt="&labelDislike;"></a>
							</span>
							<span id="shop_item_dislikes_{@id}"><xsl:value-of select="rate/@dislikes" /></span>
						</xsl:when>
						<xsl:otherwise>
							<xsl:attribute name="class">thumbs inactive</xsl:attribute>
							<span id="shop_item_likes_{@id}"><xsl:value-of select="rate/@likes" /></span>
							<span class="inner_thumbs">
								<a alt="&labelLike;"></a>
								<span class="rate" id="shop_item_rate_{@id}"><xsl:value-of select="rate" /></span>
								<a alt="&labelDislike;"></a>
							</span>
							<span id="shop_item_dislikes_{@id}"><xsl:value-of select="rate/@dislikes" /></span>
						</xsl:otherwise>
					</xsl:choose>
				</span>
			</xsl:if>
			
			<!-- Date -->
			<img src="/images/calendar.png" /> <xsl:value-of select="date"/>, <span hostcms:id="{@id}" hostcms:field="showed" hostcms:entity="shop_item"><xsl:value-of select="showed"/></span>
			<xsl:text> </xsl:text>
			<xsl:call-template name="declension">
				<xsl:with-param name="number" select="showed"/>
		</xsl:call-template><xsl:text>. </xsl:text>
		</p>
		
		
		<xsl:if test="/shop/show_comments/node() and /shop/show_comments = 1">
			
			<!-- Show Reviews -->
			<xsl:if test="count(comment)">
			<p class="h1"><a name="comments"></a>&labelReviews;</p>
				<xsl:apply-templates select="comment"/>
			</xsl:if>
		</xsl:if>
		
		<!-- If allowed to display add comment form,
		1 - Only authorized
		2 - All
		-->
		<xsl:if test="/shop/show_add_comments/node() and ((/shop/show_add_comments = 1 and /shop/siteuser_id &gt; 0)  or /shop/show_add_comments = 2)">
			
			<p class="button" onclick="$('.comment_reply').hide('slow');$('#AddComment').toggle('slow')">
				&labelAddReview;
			</p>
			
			<div id="AddComment" class="comment_reply">
				<xsl:call-template name="AddCommentForm"></xsl:call-template>
			</div>
		</xsl:if>
	</xsl:template>
	
	<!-- Шаблон для товара просмотренные -->
	<xsl:template match="shop_item" mode="view">
		<div class="shop_item">
			<div class="shop_table_item">
				<div class="image_row">
					<div class="image_cell">
						<a href="{url}">
							<xsl:choose>
								<xsl:when test="image_small != ''">
									<img src="{dir}{image_small}" alt="{name}" title="{name}"/>
								</xsl:when>
								<xsl:otherwise>
									<img src="/images/no-image.png" alt="{name}" title="{name}"/>
								</xsl:otherwise>
							</xsl:choose>
						</a>
					</div>
				</div>
				<div class="description_row">
					<div class="description_sell">
						<p>
							<a href="{url}" title="{name}" hostcms:id="{@id}" hostcms:field="name" hostcms:entity="shop_item">
								<xsl:value-of select="name"/>
							</a>
						</p>
						<div class="price">
						<xsl:value-of select="format-number(price, '### ##0,00', 'my')"/><xsl:text> </xsl:text><xsl:value-of select="currency"/><xsl:text> </xsl:text>
							<!-- Ссылку на добавление в корзины выводим, если:
							type = 0 - простой тип товара
							type = 1 - электронный товар, при этом остаток на складе больше 0 или -1,
							что означает неограниченное количество -->
							<xsl:if test="type = 0 or (type = 1 and (digitals > 0 or digitals = -1))">
								<a href="{/shop/url}cart/?add={@id}" onclick="return $.addIntoCart('{/shop/url}cart/', {@id}, 1)">
									<img src="/images/add_to_cart.gif" alt="&labelAddIntoCart;" title="&labelAddIntoCart;" />
								</a>
							</xsl:if>
							
							<!-- Сравнение товаров -->
							<xsl:variable name="shop_item_id" select="@id" />
							<div class="compare" onclick="return $.addCompare('{/shop/url}', {@id}, this)">
								<xsl:if test="/shop/comparing/shop_item[@id = $shop_item_id]/node()">
									<xsl:attribute name="class">compare current</xsl:attribute>
								</xsl:if>
							</div>
							<!-- Избранное -->
							<div class="favorite" onclick="return $.addFavorite('{/shop/url}', {@id}, this)">
								<xsl:if test="/shop/favorite/shop_item[@id = $shop_item_id]/node()">
									<xsl:attribute name="class">favorite favorite_current</xsl:attribute>
								</xsl:if>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		
		<xsl:if test="position() mod 3 = 0 and position() != last()">
			<span class="table_row"></span>
		</xsl:if>
	</xsl:template>
	
	<!-- Show property item -->
	<xsl:template match="property_value">
		<xsl:if test="value/node() and value != '' or file/node() and file != ''">
			<div class="shop_property">
				<xsl:variable name="property_id" select="property_id" />
				<xsl:variable name="property" select="/shop/shop_item_properties//property[@id=$property_id]" />
				
				<xsl:value-of select="$property/name"/><xsl:text>: </xsl:text>
				<span><xsl:choose>
						<xsl:when test="$property/type = 2">
							<a href="{../dir}{file}" target="_blank"><xsl:value-of select="file_name"/></a>
						</xsl:when>
						<xsl:when test="$property/type = 5">
							<a href="{informationsystem_item/url}"><xsl:value-of select="informationsystem_item/name"/></a>
						</xsl:when>
						<xsl:when test="$property/type = 7">
							<input type="checkbox" disabled="disabled">
								<xsl:if test="value = 1">
									<xsl:attribute name="checked">checked</xsl:attribute>
								</xsl:if>
							</input>
						</xsl:when>
						<xsl:when test="$property/type = 12">
							<a href="{shop_item/url}"><xsl:value-of select="shop_item/name"/></a>
						</xsl:when>
						<xsl:otherwise>
							<xsl:value-of disable-output-escaping="yes" select="value"/>
							<!-- Единица измерения свойства -->
							<xsl:if test="$property/shop_measure/node()">
								<xsl:text> </xsl:text><xsl:value-of select="$property/shop_measure/name"/>
							</xsl:if>
						</xsl:otherwise>
				</xsl:choose></span>
			</div>
		</xsl:if>
	</xsl:template>
	
	<!-- Tag Template -->
	<xsl:template match="tag">
		<a href="{/shop/url}tag/{urlencode}/" class="tag">
			<xsl:value-of select="name"/>
		</a>
	<xsl:if test="position() != last()"><xsl:text>, </xsl:text></xsl:if>
	</xsl:template>
	
	<!-- Шаблон для модификаций -->
	<xsl:template match="modifications/shop_item">
		<li>
			<!-- Название модификации -->
			<a href="{url}"><xsl:value-of select="name"/></a>,
			<!-- Цена модификации -->
			<xsl:value-of select="price"/><xsl:text> </xsl:text><xsl:value-of disable-output-escaping="yes" select="currency"/>
		</li>
	</xsl:template>
	
	<!-- Шаблон для сопутствующих товаров -->
	<xsl:template match="associated/shop_item">
		<li>
			<!-- Название сопутствующего товара -->
			<a href="{url}"><xsl:value-of select="name"/></a>,
			<!-- Цена сопутствующего товара -->
			<xsl:value-of select="price"/><xsl:text> </xsl:text><xsl:value-of disable-output-escaping="yes" select="currency"/>
		</li>
	</xsl:template>
	
	<!-- Star Rating -->
	<xsl:template name="show_average_grade">
		<xsl:param name="grade" select="0"/>
		<xsl:param name="const_grade" select="0"/>
		
		<!-- To avoid loops -->
		<xsl:variable name="current_grade" select="$grade * 1"/>
		
		<xsl:choose>
			<!-- If a value is an integer -->
			<xsl:when test="floor($current_grade) = $current_grade and not($const_grade &gt; ceiling($current_grade))">
				
				<xsl:if test="$current_grade - 1 &gt; 0">
					<xsl:call-template name="show_average_grade">
						<xsl:with-param name="grade" select="$current_grade - 1"/>
						<xsl:with-param name="const_grade" select="$const_grade - 1"/>
					</xsl:call-template>
				</xsl:if>
				
				<xsl:if test="$current_grade != 0">
					<img src="/images/star-full.png"/>
				</xsl:if>
			</xsl:when>
			<xsl:when test="$current_grade != 0 and not($const_grade &gt; ceiling($current_grade))">
				
				<xsl:if test="$current_grade - 0.5 &gt; 0">
					<xsl:call-template name="show_average_grade">
						
						<xsl:with-param name="grade" select="$current_grade - 0.5"/>
						<xsl:with-param name="const_grade" select="$const_grade - 1"/>
					</xsl:call-template>
				</xsl:if>
				
				<img src="/images/star-half.png"/>
			</xsl:when>
			
			<!-- Show the gray stars until the current position does not reach the value increased to an integer -->
			<xsl:otherwise>
				<xsl:call-template name="show_average_grade">
					<xsl:with-param name="grade" select="$current_grade"/>
					<xsl:with-param name="const_grade" select="$const_grade - 1"/>
				</xsl:call-template>
				<img src="/images/star-empty.png"/>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
	
	<!-- Шаблон для вывода звездочек (оценки) -->
	<xsl:template name="for">
		<xsl:param name="i" select="0"/>
		<xsl:param name="n"/>
		
		<input type="radio" name="shop_grade" value="{$i}" id="id_shop_grade_{$i}">
			<xsl:if test="/shop/shop_grade = $i">
				<xsl:attribute name="checked"></xsl:attribute>
			</xsl:if>
	</input><xsl:text> </xsl:text>
		<label for="id_shop_grade_{$i}">
			<xsl:call-template name="show_average_grade">
				<xsl:with-param name="grade" select="$i"/>
				<xsl:with-param name="const_grade" select="5"/>
			</xsl:call-template>
		</label>
		<br/>
		<xsl:if test="$n &gt; $i and $n &gt; 1">
			<xsl:call-template name="for">
				<xsl:with-param name="i" select="$i + 1"/>
				<xsl:with-param name="n" select="$n"/>
			</xsl:call-template>
		</xsl:if>
	</xsl:template>
	
	<!-- Review template -->
	<xsl:template match="comment">
		<!-- Text or subject is not empty -->
		<xsl:if test="text != '' or subject != ''">
			<a name="comment{@id}"></a>
			<div class="comment" id="comment{@id}">
				<xsl:if test="subject != ''">
					<div class="subject" hostcms:id="{@id}" hostcms:field="subject" hostcms:entity="comment"><xsl:value-of select="subject"/></div>
				</xsl:if>
				
				<div hostcms:id="{@id}" hostcms:field="text" hostcms:entity="comment" hostcms:type="wysiwyg"><xsl:value-of select="text" disable-output-escaping="yes"/></div>
				
				<p class="tags">
					<!-- Grade -->
					<xsl:if test="grade != 0">
						<span><xsl:call-template name="show_average_grade">
								<xsl:with-param name="grade" select="grade"/>
								<xsl:with-param name="const_grade" select="5"/>
						</xsl:call-template></span>
					</xsl:if>
					
					<img src="/images/user.png" />
					<xsl:choose>
						<!-- Review was added an authorized user -->
						<xsl:when test="count(siteuser) &gt; 0">
						<span><a href="/users/info/{siteuser/path}/"><xsl:value-of select="siteuser/login"/></a></span>
						</xsl:when>
						<!-- Review was added an unauthorized user -->
						<xsl:otherwise>
							<span><xsl:value-of select="author" /></span>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:if test="rate/node()">
						<span id="comment_id_{@id}" class="thumbs">
							<xsl:choose>
								<xsl:when test="/shop/siteuser_id > 0">
									<xsl:choose>
										<xsl:when test="vote/value = 1">
											<xsl:attribute name="class">thumbs up</xsl:attribute>
										</xsl:when>
										<xsl:when test="vote/value = -1">
											<xsl:attribute name="class">thumbs down</xsl:attribute>
										</xsl:when>
									</xsl:choose>
									
									<span id="comment_likes_{@id}"><xsl:value-of select="rate/@likes" /></span>
									<span class="inner_thumbs">
										<a onclick="return $.sendVote({@id}, 1, 'comment')" href="{/shop/url}?id={@id}&amp;vote=1&amp;entity_type=comment" alt="&labelLike;"></a>
										<span class="rate" id="comment_rate_{@id}"><xsl:value-of select="rate" /></span>
										<a onclick="return $.sendVote({@id}, 0, 'comment')" href="{/shop/url}?id={@id}&amp;vote=0&amp;entity_type=comment" alt="&labelDislike;"></a>
									</span>
									<span id="comment_dislikes_{@id}"><xsl:value-of select="rate/@dislikes" /></span>
								</xsl:when>
								<xsl:otherwise>
									<xsl:attribute name="class">thumbs inactive</xsl:attribute>
									<span id="comment_likes_{@id}"><xsl:value-of select="rate/@likes" /></span>
									<span class="inner_thumbs">
										<a alt="&labelLike;"></a>
										<span class="rate" id="comment_rate_{@id}"><xsl:value-of select="rate" /></span>
										<a alt="&labelDislike;"></a>
									</span>
									<span id="comment_dislikes_{@id}"><xsl:value-of select="rate/@dislikes" /></span>
								</xsl:otherwise>
							</xsl:choose>
						</span>
					</xsl:if>
					
					<img src="/images/calendar.png" /> <span><xsl:value-of select="datetime"/></span>
					
					<xsl:if test="/shop/show_add_comments/node()
						and ((/shop/show_add_comments = 1 and /shop/siteuser_id > 0)
						or /shop/show_add_comments = 2)">
					<span class="red" onclick="$('.comment_reply').hide('slow');$('#cr_{@id}').toggle('slow')">&labelReply;</span></xsl:if>
					
				<span class="red"><a href="{/shop/shop_item/url}#comment{@id}" title="&labelCommentLink;">#</a></span>
				</p>
			</div>
			
			<!-- Only for authorized users -->
			<xsl:if test="/shop/show_add_comments/node() and ((/shop/show_add_comments = 1 and /shop/siteuser_id > 0) or /shop/show_add_comments = 2)">
				<div class="comment_reply" id="cr_{@id}">
					<xsl:call-template name="AddCommentForm">
						<xsl:with-param name="id" select="@id"/>
					</xsl:call-template>
				</div>
			</xsl:if>
			
			<!-- Child Reviews -->
			<xsl:if test="count(comment)">
				<div class="comment_sub">
					<xsl:apply-templates select="comment"/>
				</div>
			</xsl:if>
		</xsl:if>
	</xsl:template>
	
	<!-- AddCommentForm Template -->
	<xsl:template name="AddCommentForm">
		<xsl:param name="id" select="0"/>
		
		
		<xsl:variable name="subject">
			<xsl:if test="/shop/comment/parent_id/node() and /shop/comment/parent_id/node() and /shop/comment/parent_id= $id">
				<xsl:value-of select="/shop/comment/subject"/>
			</xsl:if>
		</xsl:variable>
		<xsl:variable name="email">
			<xsl:if test="/shop/comment/email/node() and /shop/comment/parent_id/node() and /shop/comment/parent_id= $id">
				<xsl:value-of select="/shop/comment/email"/>
			</xsl:if>
		</xsl:variable>
		<xsl:variable name="phone">
			<xsl:if test="/shop/comment/phone/node() and /shop/comment/parent_id/node() and /shop/comment/parent_id= $id">
				<xsl:value-of select="/shop/comment/phone"/>
			</xsl:if>
		</xsl:variable>
		<xsl:variable name="text">
			<xsl:if test="/shop/comment/text/node() and /shop/comment/parent_id/node() and /shop/comment/parent_id= $id">
				<xsl:value-of select="/shop/comment/text"/>
			</xsl:if>
		</xsl:variable>
		<xsl:variable name="name">
			<xsl:if test="/shop/comment/author/node() and /shop/comment/parent_id/node() and /shop/comment/parent_id= $id">
				<xsl:value-of select="/shop/comment/author"/>
			</xsl:if>
		</xsl:variable>
		
		<div class="comment">
			
			<form action="{/shop/shop_item/url}" name="comment_form_0{$id}" method="post">
				<!-- Only for unauthorized users -->
				<xsl:if test="/shop/siteuser_id = 0">
					
					<div class="row">
						<div class="caption">&labelName;</div>
						<div class="field">
							<input type="text" size="70" name="author" value="{$name}"/>
						</div>
					</div>
					
					<div class="row">
						<div class="caption">&labelEmail;</div>
						<div class="field">
							<input id="email{$id}" type="text" size="70" name="email" value="{$email}" />
							<div id="error_email{$id}"></div>
						</div>
					</div>
					
					<div class="row">
						<div class="caption">&labelPhone;</div>
						<div class="field">
							<input type="text" size="70" name="phone" value="{$phone}"/>
						</div>
					</div>
				</xsl:if>
				
				<div class="row">
					<div class="caption">&labelSubject;</div>
					<div class="field">
						<input type="text" size="70" name="subject" value="{$subject}"/>
					</div>
				</div>
				
				<div class="row">
					<div class="caption">&labelReview;</div>
					<div class="field">
						<textarea name="text" cols="68" rows="5" class="mceEditor"><xsl:value-of select="$text"/></textarea>
					</div>
				</div>
				
				<!-- Внешние параметры -->
				<xsl:if test="count(/shop/comment_properties/property[type != 10])">
					<xsl:apply-templates select="/shop/comment_properties/property[type != 10]"/>
				</xsl:if>
				
				<div class="row">
					<div class="caption">&labelGrade;</div>
					<div class="field stars">
						<select name="grade">
							<option value="1">Poor</option>
							<option value="2">Fair</option>
							<option value="3">Average</option>
							<option value="4">Good</option>
							<option value="5">Excellent</option>
						</select>
					</div>
				</div>
				
				<!-- Showing captcha -->
				<xsl:if test="//captcha_id != 0 and /shop/siteuser_id = 0">
					<div class="row">
						<div class="caption"></div>
						<div class="field">
							<img id="comment_{$id}" class="captcha" src="/captcha.php?id={//captcha_id}{$id}&amp;height=30&amp;width=100" title="&labelCaptchaId;" name="captcha"/>
							
							<div class="captcha">
								<img src="/images/refresh.png" /> <span onclick="$('#comment_{$id}').updateCaptcha('{//captcha_id}{$id}', 30); return false">&labelUpdateCaptcha;</span>
							</div>
						</div>
					</div>
					
					<div class="row">
						<div class="caption">
					&labelCaptchaId;<sup><font color="red">*</font></sup>
						</div>
						<div class="field">
							<input type="hidden" name="captcha_id" value="{//captcha_id}{$id}"/>
							<input type="text" name="captcha" size="15"/>
						</div>
					</div>
				</xsl:if>
				
				<xsl:if test="$id != 0">
					<input type="hidden" name="parent_id" value="{$id}"/>
				</xsl:if>
				
				<div class="row">
					<div class="caption"></div>
					<div class="field">
						<input id="submit_email{$id}" type="submit" name="add_comment" value="&labelPublish;" class="button" />
					</div>
				</div>
			</form>
		</div>
	</xsl:template>
	
	<!-- Внешние свойства -->
	<xsl:template match="comment_properties/property">
		<xsl:if test="type != 10">
			<xsl:variable name="name">property_<xsl:value-of select="@id" /></xsl:variable>
			<xsl:variable name="value"></xsl:variable>
			
			<!-- form-group или checkbox -->
			<xsl:choose>
				<!-- Флажок -->
				<xsl:when test="type = 7">
					<div class="checkbox">
						<label>
							<input type="checkbox" name="{$name}" class="property-row"/>
							<xsl:value-of select="name" />
						</label>
					</div>
				</xsl:when>
				<!-- Остальные поля -->
				<xsl:otherwise>
					<div class="row">
						<div class="caption"><xsl:value-of select="name" /></div>
						<div class="field">
							<div class="input-group full-width">
								<xsl:choose>
									<!-- Отображаем поле ввода -->
									<xsl:when test="type = 0 or type = 1">
										<input type="text" name="{$name}" value="" class="form-control property-row" />
									</xsl:when>
									<!-- Отображаем файл -->
									<xsl:when test="type = 2">
										<label for="file-upload-{position()}" class="custom-file-upload form-control">
											<input id="file-upload-{position()}" class="property-row" type="file" name="{$name}"/>
										</label>
									</xsl:when>
									<!-- Отображаем список -->
									<xsl:when test="type = 3">
										<select name="{$name}" class="form-control property-row">
											<option value="0">...</option>
											<xsl:apply-templates select="list/list_item"/>
										</select>
									</xsl:when>
									<!-- Большое текстовое поле, Визуальный редактор -->
									<xsl:when test="type = 4 or type = 6">
										<textarea name="{$name}" class="form-control property-row"></textarea>
									</xsl:when>
								</xsl:choose>
							</div>
						</div>
					</div>
				</xsl:otherwise>
			</xsl:choose>
		</xsl:if>
	</xsl:template>
	
	<xsl:template match="list/list_item">
		<!-- Отображаем список -->
		<xsl:variable name="id" select="../../@id" />
		<option value="{@id}">
		<xsl:if test="/comment/property_value[property_id=$id]/value = value"><xsl:attribute name="selected">selected</xsl:attribute></xsl:if>
			<xsl:value-of disable-output-escaping="yes" select="value"/>
		</option>
	</xsl:template>
	
	<!-- Шаблон для скидки -->
	<xsl:template match="shop_discount">
		<div class="shop_discount">
			<xsl:value-of select="name"/><xsl:text> </xsl:text>
			<span>
				<xsl:choose>
					<xsl:when test="type = 0">
						<xsl:value-of select="percent"/>%
					</xsl:when>
					<xsl:otherwise>
						<xsl:value-of select="amount"/><xsl:text> </xsl:text><xsl:value-of select="/shop/shop_currency/name"/>
					</xsl:otherwise>
				</xsl:choose>
			</span>
		</div>
	</xsl:template>
	
	<!-- Шаблон выводит хлебные крошки -->
	<xsl:template match="shop_group" mode="breadCrumbs">
		<xsl:variable name="parent_id" select="parent_id"/>
		
		<!-- Call recursively parent group -->
		<xsl:apply-templates select="//shop_group[@id=$parent_id]" mode="breadCrumbs"/>
		
		<xsl:if test="parent_id=0">
			<a href="{/shop/url}" hostcms:id="{/shop/@id}" hostcms:field="name" hostcms:entity="shop">
				<xsl:value-of select="/shop/name"/>
			</a>
		</xsl:if>
		
	<span><xsl:text> → </xsl:text></span>
		
		<a href="{url}" hostcms:id="{@id}" hostcms:field="name" hostcms:entity="shop_group">
			<xsl:value-of select="name"/>
		</a>
	</xsl:template>
	
	<!-- Declension of the numerals -->
	<xsl:template name="declension">
		
		<xsl:param name="number" select="number"/>
		
		<!-- Nominative case / Именительный падеж -->
	<xsl:variable name="nominative"><xsl:text>&labelNominative;</xsl:text></xsl:variable>
		
		<!-- Genitive singular / Родительный падеж, единственное число -->
	<xsl:variable name="genitive_singular"><xsl:text>&labelGenitiveSingular;</xsl:text></xsl:variable>
		
	<xsl:variable name="genitive_plural"><xsl:text>&labelGenitivePlural;</xsl:text></xsl:variable>
		<xsl:variable name="last_digit"><xsl:value-of select="$number mod 10"/></xsl:variable>
		<xsl:variable name="last_two_digits"><xsl:value-of select="$number mod 100"/></xsl:variable>
		
		<xsl:choose>
			<xsl:when test="$last_digit = 1 and $last_two_digits != 11">
				<xsl:value-of select="$nominative"/>
			</xsl:when>
			<xsl:when test="$last_digit = 2 and $last_two_digits != 12
				or $last_digit = 3 and $last_two_digits != 13
				or $last_digit = 4 and $last_two_digits != 14">
				<xsl:value-of select="$genitive_singular"/>
			</xsl:when>
			<xsl:otherwise>
				<xsl:value-of select="$genitive_plural"/>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
</xsl:stylesheet>