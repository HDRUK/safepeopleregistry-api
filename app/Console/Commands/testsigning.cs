using System;
using System.Net.Http;
using System.Net.Http.Headers;
using System.Security.Cryptography;
using System.Text;
using System.Text.Json;
using System.Threading.Tasks;

public class Program
{
    public static async Task Main()
    {
        var clientId = "1f7f9129-292c-413a-acdc-9224b9a619da";

        var payload = new
        {
            email = "dan.ackroyd@ghostbusters.com"
        };

        var payloadJson = JsonSerializer.Serialize(payload);
        var signature = Convert.ToBase64String(CreateHmacSha256Signature(payloadJson, clientId));

        using (var client = new HttpClient())
        {
            client.DefaultRequestHeaders.Add("x-client-id", clientId);
            client.DefaultRequestHeaders.Add("x-signature", signature);
            client.DefaultRequestHeaders.Accept.Add(new MediaTypeWithQualityHeaderValue("application/json"));

            var content = new StringContent(payloadJson, Encoding.UTF8, "application/json");
            var response = await client.PostAsync("http://localhost:8100/api/v1/users/validate", content);
            var responseJson = await response.Content.ReadAsStringAsync();
            Console.WriteLine(responseJson);
        }
    }

    private static byte[] CreateHmacSha256Signature(string data, string key)
    {
        using (var hmac = new HMACSHA256(Encoding.UTF8.GetBytes(key)))
        {
            return hmac.ComputeHash(Encoding.UTF8.GetBytes(data));
        }
    }
}