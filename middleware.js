export const config = {
  matcher: '/'
};

const USERNAME = 'admin'; // 设置你的用户名
const PASSWORD = '123'; // 设置你的密码

export default function middleware(request) {
  const basicAuth = request.headers.get('authorization') || '';
  const encodedCredentials = basicAuth.replace('Basic ', '');
  const credentials = Buffer.from(encodedCredentials, 'base64').toString('utf8');
  const [username, password] = credentials.split(':');

  if (username !== USERNAME || password !== PASSWORD) {
    return new Response('Authentication required', {
      status: 401,
      headers: {
        'WWW-Authenticate': 'Basic realm="Secure Area"'
      }
    });
  }
}
